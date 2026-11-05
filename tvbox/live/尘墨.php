#EXTM3U
#EXTINF:-1 tvg-name="尘墨",尘墨
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://api.iconify.design/humbleicons:brand-github.svg">
    <title>尘墨影视接口调用</title>

    <style>
        body {
            font-family: "Microsoft YaHei", sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .notice-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px 20px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            transform: translateY(50px);
            opacity: 0;
            animation: slideUp 1s ease forwards;
            width: 90%;
            max-width: 600px;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .title {
            color: #333;
            text-align: center;
            font-size: 28px;
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleGlow 2s ease-in-out infinite;
        }

        @keyframes titleGlow {
            0%, 100% { filter: brightness(100%); }
            50% { filter: brightness(120%); }
        }

        .title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, #ff6b6b, #ff8e53);
            animation: lineWidth 2s ease-in-out infinite;
        }

        @keyframes lineWidth {
            0%, 100% { width: 80px; }
            50% { width: 120px; }
        }

        .content {
            line-height: 1.8;
            color: #2c3e50;
            font-size: 15px;
            text-align: center;
        }

        .date {
            font-weight: bold;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding: 4px 8px;
            border-radius: 6px;
            position: relative;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0 5px;
        }

        .date::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,107,107,0.1);
            border-radius: 6px;
            z-index: -1;
            transform: scale(1);
            transition: all 0.3s ease;
        }

        .date:hover::before {
            transform: scale(1.1);
            background: rgba(255,107,107,0.2);
        }

        p {
            margin: 20px 0;
            opacity: 0;
            transform: translateX(-30px);
            animation: slideIn 0.5s ease forwards;
            padding: 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
        }

        p:hover {
            background: rgba(255,255,255,0.5);
            transform: translateX(5px);
        }

        @keyframes slideUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes slideIn {
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .content p:nth-child(1) { animation-delay: 0.5s; }
        .content p:nth-child(2) { animation-delay: 0.7s; }
        .content p:nth-child(3) { animation-delay: 0.9s; }
        .content p:nth-child(4) { animation-delay: 1.1s; }
        .content p:nth-child(5) { animation-delay: 1.3s; }

        /* 添加光晕效果 */
        .notice-box::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53, #23a6d5, #23d5ab);
            border-radius: 22px;
            z-index: -1;
            filter: blur(15px);
            opacity: 0.5;
            animation: borderGlow 3s ease-in-out infinite;
        }

        @keyframes borderGlow {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.8; }
        }

        /* 添加媒体查询适配移动端 */
        @media screen and (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .notice-box {
                padding: 25px 15px;
            }
            
            .title {
                font-size: 24px;
                margin-bottom: 20px;
            }
            
            .content {
                font-size: 14px;
            }
            
            p {
                margin: 15px 0;
                padding: 8px;
            }
            
            .date {
                font-size: 13px;
                padding: 3px 6px;
            }
        }

        /* 添加音乐控制按钮样式 */
        .music-control {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .music-control:hover {
            transform: scale(1.1);
        }

        .music-control.playing {
            animation: rotate 5s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* 移动端适配 */
        @media screen and (max-width: 480px) {
            .music-control {
                width: 35px;
                height: 35px;
                top: 15px;
                right: 15px;
            }

            .music-icon {
                width: 20px;
                height: 20px;
            }
        }

        .target-url {
            color: #23a6d5;
            text-decoration: underline;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .target-url:hover {
            background: rgba(35, 166, 213, 0.1);
        }

        .copy-tip {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        #toast {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 20px;
            border-radius: 20px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>


    <div class="notice-box">
        <h1 class="title">尘墨影视接口</h1>
        <div class="content" id="normalContent">
                        <p>欢迎使用尘墨影视接口Api</p>
             <p>本站接口将于5月5日暂停使用</p>
             <p>新接口已测试开启请点击下方个人主页前往注册</p>
            <p>授权联系微信：My_lingserendipity</p>
            <a href="https://qm.qq.com/q/sfGx9e4fok" target="_blank">接口交流群</a>&nbsp;&nbsp;&nbsp;
            <a href="https://52xiaobai.cn/" target="_blank">个人主页</a>
            <p class="copy-tip">(点击网址可复制)</p>
        </div>
        <div class="content" id="weixinTip" style="display: none;">
            <p>欢迎使用尘墨影视接口Api</p>
             <p>本站接口将于5月5日暂停使用</p>
             <p>新接口已测试开启请点击下方个人主页前往注册</p>
            <p>授权联系微信：My_lingserendipity</p>
        <a href="https://qm.qq.com/q/sfGx9e4fok" target="_blank">接口交流群</a>&nbsp;&nbsp;&nbsp;
           <a href="https://52xiaobai.cn/" target="_blank">个人主页</a>
            <p class="copy-tip">(点击网址可复制)</p>
        </div>
    </div>
    <div id="toast">已复制到剪贴板</div>

    <script>
        // 修改音乐控制相关代码
        const audio = document.getElementById('bgMusic');
        const musicControl = document.querySelector('.music-control');
        let isPlaying = false;

        // 新增：处理音频播放
        async function playAudio() {
            try {
                // 设置初始音量为0以实现淡入效果
                audio.volume = 0;
                await audio.play();
                isPlaying = true;
                musicControl.classList.add('playing');
                // gradually increase volume
                let vol = 0;
                const fadeIn = setInterval(() => {
                    if (vol < 1) {
                        vol += 0.1;
                        audio.volume = Math.min(vol, 1);
                    } else {
                        clearInterval(fadeIn);
                    }
                }, 200);
            } catch (err) {
                console.log("播放失败:", err);
            }
        }

        // 新增：多种方式触发播放
        function initAudioPlay() {
            // 尝试播放
            playAudio();
            
            // 监听页面可见性变化
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden && !isPlaying) {
                    playAudio();
                }
            });
            
            // 监听用户交互
            const userInteractions = ['click', 'touchstart', 'keydown'];
            userInteractions.forEach(event => {
                document.addEventListener(event, () => {
                    if (!isPlaying) {
                        playAudio();
                    }
                }, { once: true });
            });
        }

        // 页面加载完成后初始化
        window.addEventListener('load', initAudioPlay);



// 复制网址功能
function copyUrl(url) {
    const textarea = document.createElement('textarea');
    textarea.value = url;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);

    // 显示提示
    const toast = document.getElementById('toast');
    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 2000);
}

        // 页面加载完成后的处理
        window.onload = function() {
            initAudioPlay();
            
            if (isWeixinOrQQ()) {
                // 在微信或QQ中打开
                document.getElementById('normalContent').style.display = 'none';
                document.getElementById('weixinTip').style.display = 'block';
            } else {
                // 正常浏览器中打开
                document.getElementById('weixinTip').style.display = 'none';
                startCountdown();
            }
        };


    </script>
</body>
</html>