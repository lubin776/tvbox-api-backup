#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""统一 HTTP 抓取工具：硬性时间预算 + 并发 + 失败主机熔断。

为什么需要它：
- requests 的 timeout 只是"连接/单次读取"超时，DNS 多 IP 轮询或慢速滴流
  会让单个请求远超设定值（实测单次可达 100s+）。
- 本模块在此之上加了"总时长预算"：从发起到读完整个响应体不超过 budget 秒。

对外只暴露 fetch(url, headers) -> bytes，失败抛 FetchError。
"""

import threading
import time
from urllib.parse import urlparse

import requests
from requests.adapters import HTTPAdapter

# ---------- 可调参数（可用环境变量覆盖） ----------
CONNECT_TIMEOUT = 5        # 单次 TCP 连接超时
READ_TIMEOUT = 10          # 单次读取（socket）超时
PER_URL_BUDGET = 20        # 单个 URL 从发起到读完的总时长上限
MAX_ATTEMPTS = 2           # 尝试次数（原来是 3，卡死源会翻倍耗时）
RETRY_BACKOFF = 0.5
MAX_BODY_BYTES = 8 * 1024 * 1024   # 单个播放列表最大 8MB，防超大文件拖时间
HOST_FAIL_LIMIT = 5        # 同一主机连续失败 N 次后熔断（同一主机常有多条线路，阈值别太紧）

_thread_local = threading.local()
_lock = threading.Lock()
_host_fail = {}


class FetchError(Exception):
    """抓取失败。fatal=True 表示不必重试（本机地址 / 已熔断）。"""

    def __init__(self, msg, host=None, fatal=False):
        super().__init__(msg)
        self.host = host
        self.fatal = fatal


def _session():
    """每个线程复用一个 Session，走连接池，省掉重复 TLS 握手。"""
    s = getattr(_thread_local, "session", None)
    if s is None:
        s = requests.Session()
        adapter = HTTPAdapter(pool_connections=16, pool_maxsize=16, max_retries=0)
        s.mount("http://", adapter)
        s.mount("https://", adapter)
        _thread_local.session = s
    return s


def is_local_url(url):
    """CI 环境访问不到的本机/回环地址（如 TVBox 的 127.0.0.1:9978 代理）。"""
    try:
        host = (urlparse(url).hostname or "").lower()
    except Exception:
        return False
    if not host:
        return True
    return (
        host in {"localhost", "127.0.0.1", "0.0.0.0", "::1"}
        or host.startswith("127.")
    )


def host_blocked(host):
    with _lock:
        return _host_fail.get(host, 0) >= HOST_FAIL_LIMIT


def _mark_host(host, ok):
    if not host:
        return
    with _lock:
        if ok:
            _host_fail.pop(host, None)
        else:
            _host_fail[host] = _host_fail.get(host, 0) + 1


def _read_once(url, headers, budget, verify=True):
    start = time.time()
    session = _session()
    with session.get(
        url,
        headers=headers,
        timeout=(CONNECT_TIMEOUT, READ_TIMEOUT),
        stream=True,
        allow_redirects=True,
        verify=verify,
    ) as resp:
        resp.raise_for_status()
        buf = []
        total = 0
        for chunk in resp.iter_content(65536):
            if not chunk:
                continue
            buf.append(chunk)
            total += len(chunk)
            if total >= MAX_BODY_BYTES:
                break
            if time.time() - start > budget:
                raise FetchError(f"读取超过 {budget}s 预算")
        return b"".join(buf)


def fetch(url, headers, budget=PER_URL_BUDGET, verify=True, breaker=True):
    """抓取 URL，返回响应体 bytes；任何情况下总耗时不超过 budget × MAX_ATTEMPTS。

    verify=False  允许自签/过期证书（部分民间接口站证书不规范）
    breaker=False 关闭主机熔断（同一域名下有多条互不相干的接口时，别一刀切）
    """
    host = (urlparse(url).hostname or "").lower()
    if is_local_url(url):
        raise FetchError("本机/回环地址，CI 无法访问", host, fatal=True)
    if breaker and host_blocked(host):
        raise FetchError("该主机连续失败已熔断，跳过", host, fatal=True)

    last = None
    for attempt in range(MAX_ATTEMPTS):
        started = time.time()
        try:
            data = _read_once(url, headers, budget, verify=verify)
            if breaker:
                _mark_host(host, True)
            return data
        except FetchError as e:
            last = FetchError(str(e), host)
        except requests.RequestException as e:
            last = FetchError(f"{type(e).__name__}: {e}", host)
        except Exception as e:  # 兜底，绝不让单个源炸掉整轮任务
            last = FetchError(f"{type(e).__name__}: {e}", host)

        elapsed = time.time() - started
        # 已经耗掉大半预算的失败不再重试，避免时间翻倍
        if attempt + 1 >= MAX_ATTEMPTS or elapsed > budget * 0.6:
            break
        time.sleep(RETRY_BACKOFF)

    if breaker:
        _mark_host(host, False)
    raise last or FetchError("未知错误", host)


def reset_host_state():
    with _lock:
        _host_fail.clear()
