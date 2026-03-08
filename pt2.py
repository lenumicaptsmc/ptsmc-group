<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="title">Heartopia Painting Tools</title>

    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/site_icon.png" />

    <meta name="title" data-i18n="title" content="Heartopia Painting Tools" />
    <meta name="description" data-i18n="toolsInfoContent"
        content="This tool is designed for Heartopia players, automatically converting uploaded images into pixel art format suitable for in-game painting, helping players easily create pixel-style artworks. When the developer reached level 14 in the game, they experienced the challenges of painting within the game firsthand, which inspired the creation of this program to help other art-loving players create more easily." />

    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://zerochansy.github.io/Heartopia-Painting-Tools/" />
    <meta property="og:title" data-i18n="title" content="Heartopia Painting Tools" />
    <meta property="og:description" data-i18n="toolsInfoContent"
        content="This tool is designed for Heartopia players, automatically converting uploaded images into pixel art format suitable for in-game painting, helping players easily create pixel-style artworks." />
    <meta property="og:image" content="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/seo.jpg" />

    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="https://zerochansy.github.io/Heartopia-Painting-Tools/" />
    <meta property="twitter:title" data-i18n="title" content="Heartopia Painting Tools" />
    <meta property="twitter:description" data-i18n="toolsInfoContent"
        content="This tool is designed for Heartopia players, automatically converting uploaded images into pixel art format suitable for in-game painting, helping players easily create pixel-style artworks." />
    <meta property="twitter:image" content="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/seo.jpg" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Chiron+GoRound+TC:wght@200..900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">

    <style>
        /* ============================================
           HEARTOPIA PAINTING TOOLS - MAIN STYLES
           ============================================ */
        :root {
            /* REFINED COLORS FOR SMOOTHER UI */
            --primary-color: #6B4D24;
            --bg-color: #FCF9F0;
            --border-color: #F8DCAE;
            --progress-bg: #EAE0CD;
            --progress-border: #CDB99F;
            --active-bg: #FFFBF5;
            --active-text: #8A6D6C;
            --text-color: #6B4D24;
            --toolbar-fill: #613736;
            --design-width: 1920;
            --container-width: min(100dvw, calc(100dvh * 1920 / 1080));
            --scale: calc(var(--container-width) / var(--design-width) / 1px);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        html, body {
            width: 100dvw;
            height: 100dvh;
            font-family: 'Chiron GoRound TC', sans-serif;
            font-weight: 700;
            font-size: calc(36px * var(--scale));
            color: var(--text-color);
            background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/bg.jpg') no-repeat center bottom;
            background-size: cover;
            background-position: bottom right;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        html[lang="en"] body,
        html[lang="th"] body {
            font-size: calc(24px * var(--scale));
        }

        .app-container {
            width: min(100dvw, calc(100dvh * 16 / 9));
            height: min(100dvh, calc(100dvw * 9 / 16));
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(0, 0, 0, 0);
            position: relative;
        }

        img { width: 100%; height: 100%; }

        .lang-btn, .btn-home {
            position: absolute;
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            top: 3%;
            width: calc(150px * var(--scale));
            z-index: 10;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .lang-btn { right: 3%; }
        .btn-home { left: 3%; }

        .info-btn {
            border: none;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            height: calc(60px * var(--scale));
            width: calc(60px * var(--scale));
            margin-left: calc(20px * var(--scale));
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .lang-icon, .info-btn svg {
            width: 100%;
            height: 100%;
            fill: var(--text-color);
            stroke: var(--text-color);
        }

        .lang-btn:hover, .info-btn:hover, .btn-home:hover {
            opacity: 1;
            transform: scale(1.15) translateY(-3px);
            filter: drop-shadow(0 6px 12px rgba(0,0,0,0.2));
        }

        /* NEW: Smooth Step Transition */
        @keyframes stepFadeIn {
            from { opacity: 0; transform: scale(0.97) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .step {
            aspect-ratio: 16 / 9;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100%;
            animation: stepFadeIn 0.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }

        .step.hidden {
            display: none !important;
            opacity: 0;
            pointer-events: none;
        }

        .panel {
            width: calc(1262px * var(--scale));
            height: calc(750px * var(--scale));
            border-radius: 40px;
            background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/home_board_bg.png') no-repeat center;
            background-size: contain;
            display: flex;
            flex-direction: column;
            padding: calc(80px * var(--scale));
            position: relative;
            /* Enhanced Drop Shadow for 3D feel */
            filter: drop-shadow(0 25px 45px rgba(0, 0, 0, 0.25));
            transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .title-bar {
            width: fit-content;
            height: calc(80px * var(--scale));
            padding: 0px calc(40px * var(--scale));
            background-color: var(--bg-color);
            border: calc(2px * var(--scale)) solid var(--border-color);
            border-radius: calc(20px * var(--scale));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: calc(20px * var(--scale));
            flex-shrink: 0;
            position: absolute;
            top: 0%;
            left: 50%;
            transform: translate(-50%, -80%);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.4s ease;
        }

        .title-bar:hover {
            transform: translate(-50%, -85%) scale(1.02);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .title-bar h1 {
            font-size: calc(36px * var(--scale));
            color: var(--text-color);
        }
        html[lang="en"] .title-bar h1,
        html[lang="th"] .title-bar h1 {
            font-size: calc(24px * var(--scale));
        }

        .progress-bar {
            position: absolute;
            width: calc(650px * var(--scale));
            left: 50%;
            transform: translateX(-50%);
            top: 9%;
            height: calc(80px * var(--scale));
            background-color: var(--progress-bg);
            border: calc(2px * var(--scale)) solid var(--progress-border);
            border-radius: calc(20px * var(--scale));
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: calc(10px * var(--scale));
            gap: calc(20px * var(--scale));
            flex-shrink: 0;
            box-shadow: inset 0 2px 5px rgba(255,255,255,0.5), 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .progress-step {
            height: calc(60px * var(--scale));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
            transition: all 0.4s ease;
            flex: 1;
            border-radius: calc(10px * var(--scale));
        }

        .progress-step.active {
            background-color: var(--active-bg);
            color: var(--active-text);
            box-shadow: 0 4px 10px rgba(0,0,0,0.06), inset 0 2px 4px rgba(255,255,255,0.8);
            transform: scale(1.02);
        }

        .progress-step.completed {
            opacity: 0.6;
            color: var(--text-color);
        }

        .size-container {
            margin-top: calc(40px * var(--scale));
            flex: 1;
            background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/size_bg.png') no-repeat center;
            background-size: contain;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .size-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: calc(40px * var(--scale));
            flex-wrap: nowrap;
            width: 80%;
        }

        .size-item {
            position: relative;
            cursor: pointer;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), filter 0.4s ease;
            width: calc(140px * var(--scale));
            height: calc(140px * var(--scale));
        }

        .size-item:hover {
            transform: scale(1.12) translateY(-5px);
            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.15));
        }

        .size-item img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            position: absolute;
        }

        .size-item-selected {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/size_item_selected.png') no-repeat center;
            background-size: contain;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .size-item[data-selected="true"] .size-item-selected { opacity: 1; }
        .size-item[data-selected="true"] { 
            transform: scale(1.08); 
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.2));
        }

        .level-container {
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-bottom: calc(20px * var(--scale));
            flex-shrink: 0;
        }

        .level-selector, .grid-info {
            background-color: var(--progress-bg);
            border: calc(2px * var(--scale)) solid var(--progress-border);
            border-radius: calc(20px * var(--scale));
            padding: calc(15px * var(--scale));
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: calc(10px * var(--scale));
            box-shadow: inset 0 2px 5px rgba(255,255,255,0.4), 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .level-selector:hover, .grid-info:hover {
            transform: translateY(-2px);
            box-shadow: inset 0 2px 5px rgba(255,255,255,0.6), 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .grid-info { width: calc(200px * var(--scale)); }

        .level-selector h3, .grid-info h3 {
            font-size: calc(16px * var(--scale));
            color: var(--text-color);
        }

        .level-items {
            display: flex;
            gap: calc(10px * var(--scale));
        }

        .level-item {
            width: calc(100px * var(--scale));
            height: calc(60px * var(--scale));
            background: transparent;
            border-radius: calc(20px * var(--scale));
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .level-item:hover {
            transform: scale(1.08) translateY(-2px);
            background-color: rgba(255, 246, 234, 0.7);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .level-item[data-selected="true"] { 
            background-color: var(--active-bg);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1), inset 0 2px 5px rgba(255,255,255,0.8);
            transform: scale(1.05);
        }

        .level-item img {
            width: calc(50px * var(--scale));
            height: calc(50px * var(--scale));
            object-fit: contain;
        }

        .grid-display {
            font-size: calc(24px * var(--scale));
            color: var(--active-text);
            font-weight: bold;
        }

        .btn-next {
            width: calc(303px * var(--scale));
            height: calc(95px * var(--scale));
            background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/start_btn.png') no-repeat center;
            background-size: contain;
            border: none;
            color: #FFFFFF;
            font-family: 'Chiron GoRound TC', sans-serif;
            font-weight: 700;
            font-size: calc(36px * var(--scale));
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            flex-shrink: 0;
            position: absolute;
            bottom: 0%;
            left: 50%;
            transform: translate(-50%, 20%);
            filter: drop-shadow(0 8px 20px rgba(113, 63, 62, 0.35));
        }
        html[lang="en"] .btn-next,
        html[lang="th"] .btn-next {
            font-size: calc(24px * var(--scale));
        }

        .btn-next:hover:not(:disabled) { 
            transform: translate(-50%, 15%) scale(1.08);
            filter: drop-shadow(0 12px 30px rgba(113, 63, 62, 0.5));
        }
        .btn-next:disabled { 
            opacity: 0.6;
            cursor: not-allowed; 
            filter: grayscale(0.6) drop-shadow(0 5px 10px rgba(0,0,0,0.2)); 
        }

        .upload-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: calc(20px * var(--scale));
            gap: calc(30px * var(--scale));
        }

        .upload-area {
            width: 100%;
            height: 100%;
            background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/size_bg.png') no-repeat center;
            background-size: contain;
            border: calc(2px * var(--scale)) dashed var(--border-color);
            border-radius: calc(20px * var(--scale));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .upload-area:hover {
            border-color: var(--text-color);
            background-color: var(--active-bg);
            transform: scale(1.02);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        }

        .upload-icon {
            width: calc(80px * var(--scale));
            height: calc(80px * var(--scale));
            fill: var(--text-color);
            margin-bottom: calc(20px * var(--scale));
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .upload-area:hover .upload-icon {
            transform: translateY(-8px) scale(1.1);
        }

        .upload-area p {
            font-size: calc(20px * var(--scale));
            color: var(--text-color);
            text-align: center;
        }

        .image-preview {
            position: absolute;
            top: 22%;
            height: calc(500px * var(--scale));
            width: calc(1000px * var(--scale));
            border-radius: calc(20px * var(--scale));
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            animation: stepFadeIn 0.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }

        .image-preview.hidden { display: none !important; }

        .image-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .loading-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: calc(30px * var(--scale));
        }

        #loading-icon svg {
            width: calc(200px * var(--scale));
            height: calc(200px * var(--scale));
            animation: spin 1.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .loading-container p {
            font-size: calc(24px * var(--scale));
            color: var(--text-color);
        }

        .paint-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: calc(40px * var(--scale));
            gap: calc(20px * var(--scale));
        }

        .paint-toolbar {
            display: flex;
            flex-direction: row;
            gap: calc(20px * var(--scale));
            background-color: var(--bg-color);
            padding: calc(15px * var(--scale));
            border-radius: calc(20px * var(--scale));
            flex-shrink: 0;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .tool-btn {
            width: calc(60px * var(--scale));
            height: calc(60px * var(--scale));
            background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_btn_bg.svg') no-repeat center;
            background-size: contain;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }
        
        .tool-btn:hover {
            transform: scale(1.15) translateY(-3px);
            filter: drop-shadow(0 6px 10px rgba(0,0,0,0.2));
        }

        .tool-btn svg {
            width: calc(40px * var(--scale));
            height: calc(40px * var(--scale));
            transition: fill 0.3s;
        }

        .tool-btn.active {
            transform: scale(1.1);
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.25));
        }
        .tool-btn.active svg path { fill: #FFFFFF; }

        .tutorial-panel {
            width: calc(80px * var(--scale));
            background-color: #FCF9F0;
            border-radius: calc(40px * var(--scale));
            padding: calc(30px * var(--scale)) calc(15px * var(--scale));
            display: flex;
            flex-direction: column;
            gap: calc(20px * var(--scale));
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
            position: absolute;
            left: 0%;
            top: 50%;
            transform: translate(-80%, -50%);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }

        .tutorial-panel.hidden { 
            opacity: 0;
            transform: translate(-120%, -50%);
            pointer-events: none;
        }

        .tut-btn {
            width: calc(40px * var(--scale));
            height: calc(40px * var(--scale));
            background: transparent;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .tut-btn:hover:not(:disabled) {
            transform: scale(1.25);
        }

        .tut-btn svg { width: 100%; height: 100%; fill: var(--text-color); }

        .tut-step-info {
            width: calc(70px * var(--scale));
            height: calc(200px * var(--scale));
            background-color: #F8E7CD;
            border-radius: calc(35px * var(--scale));
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: calc(10px * var(--scale));
            box-shadow: inset 0 3px 8px rgba(0,0,0,0.06);
        }

        .tut-step {
            font-size: calc(20px * var(--scale));
            color: #7C6060;
            font-weight: bold;
        }

        .tut-color-display {
            width: calc(40px * var(--scale));
            height: calc(40px * var(--scale));
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .tut-color-display:hover {
            transform: scale(1.15);
        }

        .tut-current-color {
            width: calc(60px * var(--scale));
            height: calc(60px * var(--scale));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .tut-current-color svg { width: 100%; height: 100%; }

        .canvas-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: calc(20px * var(--scale));
            width: calc(1244px * var(--scale));
            height: calc(867px * var(--scale));
            background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/paint_borad_bg.png') no-repeat center;
            background-size: contain;
            padding: calc(80px * var(--scale));
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            filter: drop-shadow(0 25px 50px rgba(0,0,0,0.25));
        }

        .canvas-header {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: center;
        }

        .zoom-controls {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: calc(15px * var(--scale));
        }

        .zoom-controls input[type="range"] {
            width: calc(200px * var(--scale));
            height: calc(6px * var(--scale));
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        #zoom-display {
            font-size: calc(16px * var(--scale));
            color: var(--text-color);
            min-width: calc(60px * var(--scale));
            text-align: center;
            font-weight: bold;
        }

        #paint-canvas {
            max-width: calc(1100px * var(--scale));
            max-height: calc(630px * var(--scale));
            border: calc(2px * var(--scale)) solid var(--text-color);
            border-radius: calc(10px * var(--scale));
            background-color: white;
            cursor: crosshair;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .color-panel {
            width: calc(300px * var(--scale));
            height: calc(750px * var(--scale));
            background-color: #72564C;
            border-radius: calc(100px * var(--scale)) 0px 0px calc(100px * var(--scale));
            box-shadow: -10px 10px 30px rgba(0,0,0,0.25), inset 0 0 20px rgba(0,0,0,0.15);
            padding: calc(50px * var(--scale)) calc(30px * var(--scale));
            display: flex;
            flex-direction: column;
            gap: calc(20px * var(--scale));
            flex-shrink: 0;
            overflow: hidden;
            position: absolute;
            right: 0%;
            bottom: 11%;
            align-items: center;
            transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1);
        }

        .color-groups-slider {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: calc(10px * var(--scale));
            height: calc(120px * var(--scale));
        }

        .color-group-item {
            width: calc(50px * var(--scale));
            height: calc(100px * var(--scale));
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-radius: calc(8px * var(--scale));
        }

        .color-group-item:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }

        .color-group-item.selected {
            border-color: #FFFFFF;
            transform: scale(1.18);
            box-shadow: 0 8px 18px rgba(0,0,0,0.4);
            z-index: 2;
        }

        .color-group-cennter-line {
            width: calc(53px * var(--scale));
            height: calc(103px * var(--scale));
            border: calc(6px * var(--scale)) solid #FFF;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border-radius: calc(10px * var(--scale));
            box-shadow: 0 0 15px rgba(255,255,255,0.4);
            pointer-events: none;
        }

        .color-details {
            display: flex;
            flex-wrap: wrap;
            align-items: start;
            overflow-y: auto;
            width: 80%;
            padding-right: 5px;
        }
        
        .color-details::-webkit-scrollbar { width: calc(8px * var(--scale)); }
        .color-details::-webkit-scrollbar-track { background: transparent; }
        .color-details::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.4); border-radius: calc(10px * var(--scale)); }
        .color-details::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.6); }

        .color-detail-item {
            width: 50%;
            aspect-ratio: 1;
            border-radius: calc(10px * var(--scale));
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
        }

        .color-detail-item:hover { 
            transform: scale(1.2);
            z-index: 10;
            filter: drop-shadow(0 8px 15px rgba(0,0,0,0.35));
        }
        
        .color-detail-item svg {
            width: calc(100px * var(--scale));
            height: calc(100px * var(--scale));
        }

        .popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: opacity 0.4s ease;
        }

        .popup.hidden { 
            opacity: 0;
            pointer-events: none;
        }

        .popup-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            z-index: -1;
            transition: backdrop-filter 0.4s ease;
        }

        .popup-content {
            background-color: rgba(252, 249, 240, 0.95);
            backdrop-filter: blur(15px);
            border: calc(2px * var(--scale)) solid var(--progress-border);
            border-radius: calc(25px * var(--scale));
            padding: calc(40px * var(--scale));
            max-width: calc(600px * var(--scale));
            max-height: calc(80vh * var(--scale));
            display: flex;
            flex-direction: column;
            gap: calc(20px * var(--scale));
            z-index: 1;
            animation: popupIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }

        @keyframes popupIn {
            from { transform: scale(0.85) translateY(20px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }

        .popup-title {
            font-size: calc(24px * var(--scale));
            color: var(--text-color);
            font-weight: 700;
            text-align: center;
        }

        .lang-options {
            display: flex;
            flex-direction: column;
            gap: calc(15px * var(--scale));
        }

        .lang-option {
            width: calc(200px * var(--scale));
            height: calc(60px * var(--scale));
            background-color: #FFFFFF;
            border: none;
            border-radius: calc(20px * var(--scale));
            font-size: calc(18px * var(--scale));
            color: var(--text-color);
            font-family: 'Chiron GoRound TC', sans-serif;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            align-self: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .lang-option:hover {
            background-color: var(--active-bg);
            transform: scale(1.08) translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }

        .info-content {
            position: relative;
            max-width: calc(800px * var(--scale));
            max-height: calc(600px * var(--scale));
        }

        .btn-close {
            position: absolute;
            top: 0%;
            right: 0%;
            width: calc(100px * var(--scale));
            height: calc(100px * var(--scale));
            transform: translate(30%, -30%);
            background: transparent;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .btn-close:hover {
            transform: translate(30%, -30%) scale(1.15) rotate(90deg);
        }

        .btn-close svg {
            width: 100%;
            height: 100%;
            fill: var(--text-color);
            stroke: var(--text-color);
        }

        .info-title {
            font-size: calc(36px * var(--scale));
            color: var(--text-color);
            text-align: center;
            margin-bottom: calc(20px * var(--scale));
        }
        html[lang="en"] .info-title { font-size: calc(24px * var(--scale)); }

        .info-text {
            font-size: calc(24px * var(--scale));
            color: var(--text-color);
            line-height: 1.6;
            overflow-y: auto;
            padding-right: calc(10px * var(--scale));
        }

        .info-text p, .info-text ul {
            margin-bottom: calc(15px * var(--scale));
        }

        .info-text::-webkit-scrollbar { width: calc(10px * var(--scale)); }
        .info-text::-webkit-scrollbar-track { background: transparent; }
        .info-text::-webkit-scrollbar-thumb { background: var(--text-color); border-radius: calc(10px * var(--scale)); }
        .info-text::-webkit-scrollbar-thumb:hover { background: var(--progress-border); }

        body.no-scroll { overflow: hidden; }

        .social-links {
            display: flex;
            gap: calc(20px * var(--scale));
            align-items: center;
            margin-bottom: calc(15px * var(--scale));
        }
        .social-link {
            background: #FFF;
            padding: calc(20px * var(--scale)) calc(40px * var(--scale));
            border-radius: calc(50px * var(--scale));
            border: calc(2px * var(--scale)) solid var(--border-color);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .social-link:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
            background: var(--active-bg);
        }

        .social-link a {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: calc(10px * var(--scale));
            color: unset;
            text-decoration: none;
        }

        #version {
            font-size: calc(24px * var(--scale));
            color: #FFF;
            position: absolute;
            bottom: calc(10px * var(--scale));
            right: calc(10px * var(--scale));
            text-shadow: calc(1px * var(--scale)) calc(1px * var(--scale)) calc(4px * var(--scale)) rgba(0, 0, 0, 0.6);
            font-weight: normal;
        }

        .portrait-warning { display: none; }
        .warning-icon { max-width: 80%; margin-top: 20px; }
        .warning-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 80%;
        }
        .rotateMessageTop, .rotateMessageBottom {
            width: fit-content;
            padding: 10px 20px;
            background-color: var(--bg-color);
            border: 2px solid var(--border-color);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }

        .rotateMessageBottom {
            position: absolute;
            bottom: 0%;
            left: 0%;
            width: 100%;
            border-radius: 20px 20px 0 0 ;
            padding: 20px 20px;
        }

        .donation a { text-decoration: none; }

        @media (orientation: portrait) {
            .portrait-warning {
                width: 100dvw;
                height: 100dvh;
                position: fixed;
                top: 0;
                left: 0;
                display: flex; 
                align-items: center;
                justify-content: center;
                background: url('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/bg.jpg') no-repeat center bottom;
                background-size: cover;
                z-index: 9999;
            }
            .app-container { display: none !important; }
        }
    </style>
</head>

<body>
    <span id="version">v1.0.7</span>
    <div id="app-container" class="app-container">
        <button id="lang-btn" class="lang-btn" title="Change Language">
            <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_btn_lang.svg" alt="Language">
        </button>

        <div id="step-1" class="step">
            <div class="panel">
                <div class="title-bar">
                    <h1 data-i18n="title"></h1>
                    <button id="info-btn" class="info-btn">
                        <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_info.svg" alt="Info">
                    </button>
                </div>

                <div class="progress-bar">
                    <div class="progress-step active"><span data-i18n="step1"></span></div>
                    <div class="progress-step"><span data-i18n="step2"></span></div>
                    <div class="progress-step"><span data-i18n="step3"></span></div>
                </div>

                <div class="size-container">
                    <div class="size-grid">
                        <div class="size-item" data-ratio="16:9" data-selected="true">
                            <div class="size-item-selected"></div>
                            <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/size_item_16_9.png" alt="16:9">
                        </div>
                        <div class="size-item" data-ratio="4:3">
                            <div class="size-item-selected"></div>
                            <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/size_item_4_3.png" alt="4:3">
                        </div>
                        <div class="size-item" data-ratio="1:1">
                            <div class="size-item-selected"></div>
                            <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/size_item_1_1.png" alt="1:1">
                        </div>
                        <div class="size-item" data-ratio="3:4">
                            <div class="size-item-selected"></div>
                            <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/size_item_3_4.png" alt="3:4">
                        </div>
                        <div class="size-item" data-ratio="9:16">
                            <div class="size-item-selected"></div>
                            <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/size_item_9_16.png" alt="9:16">
                        </div>
                    </div>
                </div>

                <div class="level-container">
                    <span data-i18n="precision"></span>
                    <div class="level-selector">
                        <div class="level-items">
                            <div class="level-item" data-level="0" data-selected="true">
                                <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/level_item_1.png" alt="Small">
                            </div>
                            <div class="level-item" data-level="1">
                                <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/level_item_2.png" alt="Medium">
                            </div>
                            <div class="level-item" data-level="2">
                                <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/level_item_3.png" alt="Large">
                            </div>
                            <div class="level-item" data-level="3">
                                <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/level_item_4.png" alt="Extra Large">
                            </div>
                        </div>
                    </div>

                    <span data-i18n="gridSize"></span>
                    <div class="grid-info">
                        <div class="grid-display" id="grid-display"></div>
                    </div>
                </div>
                <button class="btn-next" onclick="app.nextStep()" data-i18n="next"></button>
            </div>
        </div>

        <div id="step-2" class="step hidden">
            <div class="panel">
                <div class="title-bar">
                    <h1 data-i18n="title"></h1>
                    <button id="info-btn" class="info-btn">
                        <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_info.svg" alt="Info">
                    </button>
                </div>

                <div class="progress-bar">
                    <div class="progress-step completed"><span data-i18n="step1"></span></div>
                    <div class="progress-step active"><span data-i18n="step2"></span></div>
                    <div class="progress-step"><span data-i18n="step3"></span></div>
                </div>

                <div class="upload-container">
                    <div id="upload-area" class="upload-area">
                        <svg class="upload-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                        </svg>
                        <p data-i18n="uploadText"></p>
                        <input type="file" id="image-input" accept="image/*" style="display: none;">
                    </div>
                    <div id="image-preview" class="image-preview hidden"></div>
                </div>
                <button class="btn-next" id="btn-step2-next" disabled onclick="app.nextStep()" data-i18n="next"></button>
            </div>
        </div>

        <div id="step-3" class="step hidden">
            <div class="panel">
                <div class="title-bar">
                    <h1 data-i18n="title"></h1>
                    <button id="info-btn" class="info-btn">
                        <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_info.svg" alt="Info">
                    </button>
                </div>
                <div class="progress-bar">
                    <div class="progress-step completed"><span data-i18n="step1"></span></div>
                    <div class="progress-step completed"><span data-i18n="step2"></span></div>
                    <div class="progress-step active"><span data-i18n="step3"></span></div>
                </div>
                <div class="loading-container">
                    <div id="loading-icon"></div>
                    <p data-i18n="loading"></p>
                </div>
            </div>
        </div>

        <div id="step-4" class="step hidden">
            <button id="btn-home" class="btn-home">
                <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_replay.svg" alt="Home">
            </button>

            <div class="paint-container">
                <div class="canvas-wrapper">
                    <div id="tutorial-panel" class="tutorial-panel hidden">
                        <button class="tut-btn" id="tut-first"></button>
                        <div class="tut-step-info">
                            <button class="tut-btn" id="tut-prev"></button>
                            <div class="tut-step" data-i18n="stepText"></div>
                            <div class="tut-step" id="tut-step-num"></div>
                            <button class="tut-btn" id="tut-next"></button>
                        </div>
                        <button class="tut-btn" id="tut-last"></button>
                        <div class="tut-current-color">
                            <div class="tut-color-display color-display" id="tut-color"></div>
                        </div>
                    </div>

                    <div class="canvas-header">
                        <div class="paint-toolbar">
                            <button id="tool-grid" class="tool-btn" title="Show Grid"></button>
                            <button id="tool-pick" class="tool-btn" title="Eyedropper"></button>
                            <button id="tool-teach" class="tool-btn" title="Tutorial"></button>
                        </div>
                        <div class="zoom-controls">
                            <input type="range" id="zoom-slider" min="1" max="10" value="1" step="0.1">
                            <span id="zoom-display">1.0x</span>
                        </div>
                    </div>
                    <canvas id="paint-canvas"></canvas>
                </div>

                <div class="color-panel">
                    <div class="color-groups-display owl-carousel" id="color-groups-display"></div>
                    <div id="color-details" class="color-details"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="lang-popup" class="popup hidden">
        <div class="popup-overlay"></div>
        <div class="popup-content">
            <div class="popup-title" data-i18n="selectLanguage"></div>
            <div class="lang-options">
                <button class="lang-option" data-lang="zh-TW">繁體中文</button>
                <button class="lang-option" data-lang="zh-CN">简体中文</button>
                <button class="lang-option" data-lang="en">English</button>
                <button class="lang-option" data-lang="th">ภาษาไทย</button>
            </div>
        </div>
    </div>

    <div id="info-popup" class="popup hidden">
        <div class="popup-overlay"></div>
        <div class="panel">
            <button class="btn-close">
                <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_btn_close.svg" alt="Close">
            </button>
            <div class="title-bar"><h1 data-i18n="infoTitle"></h1></div>
            <div class="info-text" id="info-text">
                <h2 data-i18n="toolsInfoTitle"></h2>
                <p data-i18n="toolsInfoContent"></p>
                <h2 data-i18n="mainFeaturesTitle"></h2>
                <p data-i18n="mainFeaturesContent"></p>
                <h2 data-i18n="usageInstructionsTitle"></h2>
                <p data-i18n="usageInstructionsContent"></p>
                <div class="developer-info">
                    <h2 data-i18n="developerInfoTitle"></h2>
                    <p data-i18n="developerInfoContent"></p>
                    <div class="social-links">
                        <div class="social-link">
                            <span data-i18n="gameIdLabel"></span><span id="game-id">162hkm95</span><span class="copied_text hidden" data-i18n="copiedText"></span>
                        </div>
                        <div class="social-link">
                            <a href="https://www.linkedin.com/in/zerochansy/" target="_blank">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#0A66C2">
                                    <path d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z"></path>
                                </svg>ZEROCHANSY
                            </a>
                        </div>
                        <div class="social-link">
                            <a href="https://www.instagram.com/zerochansy" target="_blank">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="#E4405F">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"></path>
                                </svg>ZEROCHANSY
                            </a>
                        </div>
                    </div>
                </div>
                <div class="donation">
                    <div class="donation-title" data-i18n="donationTitle"></div>
                    <p class="donation-desc" data-i18n="donationDesc"></p>
                    <a href="https://www.paypal.com/paypalme/zerochansy" target="_blank">
                        <p class="donation-desc" data-i18n="paypalDonation"></p>
                    </a>
                    <div>
                        <a href="#"><p id="wechat-qr" data-i18n="wechatDonation"></p></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="portrait-warning">
        <div class="warning-content">
            <div class="rotateMessageTop"><span data-i18n="rotateMessageTop"></span></div>
            <img src="https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_rotate.png" alt="Warning" class="warning-icon">
            <div class="rotateMessageBottom"><span data-i18n="rotateMessageBottom"></span></div>
        </div>
    </div>

    <div id="color-svg" class="hidden"></div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>

    <script>
        // ==========================================
        // JS - COLOR LOADER
        // ==========================================
        const colorLoader = {
            svgPath: 'https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/color/color.svg',
            colorData: null,
            async loadSVG() {
                try {
                    const response = await fetch(this.svgPath);
                    return await response.text();
                } catch (error) {
                    console.error('Error loading SVG:', error);
                    return null;
                }
            },
            extractColors(svgText) {
                const colors = {};
                const colorRegex = /id="Color-(\d+)-(\d+)"[^>]*fill="([^"]+)"/g;
                let match;
                while ((match = colorRegex.exec(svgText)) !== null) {
                    const groupNum = match[1], colorNum = match[2], fillColor = match[3];
                    if (!colors[groupNum]) colors[groupNum] = [];
                    colors[groupNum][colorNum - 1] = fillColor;
                }
                return colors;
            },
            extractGroupColors(svgText) {
                const groupColors = {};
                const groupRegex = /<(rect|circle|ellipse)[^>]*id="Color-(\d+)"[^>]*fill="([^"]+)"/g;
                let match;
                while ((match = groupRegex.exec(svgText)) !== null) {
                    groupColors[`Color-${match[2]}`] = match[3];
                }
                return groupColors;
            },
            groupColors(colorsByGroup, groupColors) {
                const organizedGroups = {};
                Object.keys(colorsByGroup).sort((a, b) => parseInt(a) - parseInt(b)).forEach(groupNum => {
                    const groupNumber = parseInt(groupNum);
                    const groupKey = `Group ${groupNumber}`;
                    const colorsArray = colorsByGroup[groupNum].filter(c => c !== undefined);
                    const mainColor = groupColors[`Color-${groupNumber}`] || colorsArray[0] || '#FFFFFF';
                    organizedGroups[groupKey] = { mainColor, colors: colorsArray };
                });
                return organizedGroups;
            },
            async initialize() {
                const svgText = await this.loadSVG();
                if (!svgText) return null;
                const colors = this.extractColors(svgText);
                const groupColors = this.extractGroupColors(svgText);
                this.colorData = this.groupColors(colors, groupColors);
                return this.colorData;
            }
        };

        // ==========================================
        // JS - LANG
        // ==========================================
        const localTranslations = {
            "en": {"title":"Heartopia Painting Tools","step1":"Select Size","step2":"Upload Image","step3":"Generate","precision":"Precision","gridSize":"Grid Size","next":"Next","back":"Back","uploadText":"Drag or click to upload image","loading":"Generating painting...","selectLanguage":"Select Language","infoTitle":"About This Application & Developer","levelSmall":"Small","levelMedium":"Medium","levelLarge":"Large","levelExtraLarge":"Extra Large","showGrid":"Show Grid","hideGrid":"Hide Grid","eyedropper":"Eyedropper","tutorial":"Tutorial","currentColor":"Current Color","zoom":"Zoom","reset":"Reset","save":"Save","stepText":"STEP","toolsInfoTitle":"Tool Introduction","toolsInfoContent":"This tool is designed for Heartopia players, automatically converting uploaded images into pixel art format suitable for in-game painting, helping players easily create pixel-style artworks. When the developer reached level 14 in the game, they experienced the challenges of painting within the game firsthand, which inspired the creation of this program to help other art-loving players create more easily.","mainFeaturesTitle":"Main Features","mainFeaturesContent":"• Multiple Image Ratios: Supports common ratios like 16:9, 4:3, 1:1, 3:4, 9:16\n• Detail Level Selection: Four precision levels to meet different painting needs\n• Game-native Colors: Uses only colors specified in the game to ensure artwork consistency\n• Painting Assistance Tools: Built-in grid display and eyedropper tool for easy reference and detail adjustment\n• Beginner-friendly: Step-by-step tutorials to help beginners get started quickly","usageInstructionsTitle":"Usage Instructions","usageInstructionsContent":"✅ Recommended to use the desktop version for the best experience.\n✅ All image processing is done <b>locally on your device</b>; no images are uploaded to any server, and no AI programs are used. Feel free to use your personal photos.","developerInfoTitle":"About the Developer","developerInfoContent":"The developer is a designer and game developer, and also a dedicated 'Heartbeat Town' player. Welcome to visit and like my game island!","gameIdLabel":"Game ID:","donationTitle":"❤️ Support the Developer","donationDesc":"If you find this tool helpful, consider supporting the developer with a donation～\nYour support gives the developer more motivation～","copiedText":"Copied","paypalDonation":"👉 PayPal Donation (Click here to go to the donation page)","wechatDonation":"👉 WeChat Pay (Click image to view payment QR code)","rotateMessageTop":"Please rotate your device to landscape mode","rotateMessageBottom":"Recommended to use the computer version for the best experience"},
            "th": {"title":"เครื่องมือวาดภาพ Heartopia","step1":"เลือกขนาด","step2":"อัปโหลดรูปภาพ","step3":"สร้างภาพ","precision":"ความละเอียด","gridSize":"ขนาดตาราง","next":"ถัดไป","back":"ย้อนกลับ","uploadText":"ลากหรือคลิกเพื่ออัปโหลดรูปภาพ","loading":"กำลังสร้างภาพวาด...","selectLanguage":"เลือกภาษา","infoTitle":"เกี่ยวกับแอปพลิเคชันและผู้พัฒนา","levelSmall":"เล็ก","levelMedium":"กลาง","levelLarge":"ใหญ่","levelExtraLarge":"ใหญ่มาก","showGrid":"แสดงตาราง","hideGrid":"ซ่อนตาราง","eyedropper":"ดูดสี","tutorial":"บทเรียน","currentColor":"สีปัจจุบัน","zoom":"ซูม","reset":"รีเซ็ต","save":"บันทึก","stepText":"ขั้นที่","toolsInfoTitle":"แนะนำเครื่องมือ","toolsInfoContent":"เครื่องมือนี้ออกแบบมาสำหรับผู้เล่น Heartopia โดยจะแปลงรูปภาพที่อัปโหลดเป็นรูปแบบพิกเซลอาร์ตที่เหมาะสำหรับการวาดในเกมโดยอัตโนมัติ ช่วยให้ผู้เล่นสร้างงานศิลปะสไตล์พิกเซลได้ง่ายขึ้น เมื่อผู้พัฒนาเล่นถึงเลเวล 14 ในเกม ได้สัมผัสกับความท้าทายในการวาดภาพในเกมด้วยตัวเอง ซึ่งเป็นแรงบันดาลใจให้สร้างโปรแกรมนี้ขึ้นมาเพื่อช่วยให้ผู้เล่นที่รักศิลปะคนอื่นๆ สร้างสรรค์ผลงานได้ง่ายขึ้น","mainFeaturesTitle":"คุณสมบัติหลัก","mainFeaturesContent":"• รองรับสัดส่วนรูปภาพหลากหลาย: รองรับสัดส่วนทั่วไปเช่น 16:9, 4:3, 1:1, 3:4, 9:16\n• เลือกระดับรายละเอียดได้: ความละเอียด 4 ระดับเพื่อตอบสนองความต้องการในการวาดภาพที่แตกต่างกัน\n• สีตามต้นฉบับเกม: ใช้เฉพาะสีที่มีในเกมเพื่อให้แน่ใจว่างานศิลปะมีความสม่ำเสมอ\n• เครื่องมือช่วยวาดภาพ: มีตารางแสดงผลและเครื่องมือดูดสีเพื่อการอ้างอิงและปรับรายละเอียดได้ง่าย\n• เหมาะสำหรับมือใหม่: มีบทเรียนทีละขั้นตอนเพื่อช่วยให้มือใหม่เริ่มต้นได้รวดเร็ว","usageInstructionsTitle":"คำแนะนำการใช้งาน","usageInstructionsContent":"✅ แนะนำให้ใช้เวอร์ชันเดสก์ท็อปเพื่อประสบการณ์ที่ดีที่สุด\n✅ การประมวลผลรูปภาพทั้งหมดทำ<b>ในเครื่องของคุณ</b> ไม่มีการอัปโหลดรูปภาพไปยังเซิร์ฟเวอร์ใดๆ และไม่มีการใช้โปรแกรม AI เชิญใช้รูปถ่ายส่วนตัวของคุณได้ตามสบาย","developerInfoTitle":"เกี่ยวกับผู้พัฒนา","developerInfoContent":"ผู้พัฒนาเป็นนักออกแบบและนักพัฒนาเกม และยังเป็นผู้เล่น 'Heartbeat Town' ตัวยง ยินดีต้อนรับสู่เกาะในเกมของฉันและกดถูกใจให้ด้วยนะ!","gameIdLabel":"รหัสเกม:","donationTitle":"❤️ สนับสนุนผู้พัฒนา","donationDesc":"หากคุณพบว่าเครื่องมือนี้มีประโยชน์ โปรดพิจารณาสนับสนุนผู้พัฒนาด้วยการบริจาค～\nการสนับสนุนของคุณช่วยให้ผู้พัฒนามีแรงจูงใจมากขึ้น～","copiedText":"คัดลอกแล้ว","paypalDonation":"👉 บริจาคผ่าน PayPal (คลิกที่นี่เพื่อไปหน้าบริจาค)","wechatDonation":"👉 WeChat Pay (คลิกที่รูปเพื่อดู QR code ชำระเงิน)","rotateMessageTop":"กรุณาหมุนอุปกรณ์เป็นแนวนอน","rotateMessageBottom":"แนะนำให้ใช้เวอร์ชันคอมพิวเตอร์เพื่อประสบการณ์ที่ดีที่สุด"},
            "zh-CN": {"title":"心动小镇绘画工具","step1":"选择尺寸","step2":"上载图片","step3":"生成绘画","precision":"精细度","gridSize":"格数","next":"下一步","back":"返回","uploadText":"拖曳或点击上传图片","loading":"正在生成绘画...","selectLanguage":"选择语言","infoTitle":"程序简介及开发人员","levelSmall":"小","levelMedium":"中","levelLarge":"大","levelExtraLarge":"超大","showGrid":"显示网格","hideGrid":"隐藏网格","eyedropper":"滴管工具","tutorial":"教程","currentColor":"当前颜色","zoom":"缩放","reset":"重置","save":"保存","stepText":"步骤","toolsInfoTitle":"工具介绍","toolsInfoContent":"本工具专为《心动小镇》玩家设计，能将上传的图片自动转换为游戏内绘画适用的点阵图格式，帮助玩家轻松创作像素风格画作。开发者刚升级至游戏14级时，深感在游戏内绘画的挑战，因而开发此程序，希望帮助同样喜爱绘画的玩家更轻松地创作。","mainFeaturesTitle":"主要功能","mainFeaturesContent":"• 多种图片比例：支持 16:9、4:3、1:1、3:4、9:16 等常用比例\n• 精细度选择：四种等级设定，满足不同绘画需求\n• 游戏原生色彩：仅使用游戏内指定颜色进行转换，确保作品风格一致\n• 绘画辅助工具：内置网格显示与滴管工具，便于对照与细节调整\n• 新手友好：提供分步教程，协助初学者快速上手","usageInstructionsTitle":"使用说明","usageInstructionsContent":"✅ 建议使用电脑版本以获得最佳体验效果。\n✅ 本工具所有图片处理均在<b>本地进行</b>，不会上传至任何服务器，亦不使用任何AI程序，请放心使用个人照片。","developerInfoTitle":"关于开发者","developerInfoContent":"开发者是一名设计师兼游戏开发者，也是《心动小镇》的忠实玩家。欢迎来我的游戏小岛点赞！","gameIdLabel":"游戏ID：","donationTitle":"❤️ 支持开发者","donationDesc":"如果觉得工具对你有帮助，欢迎打赏支持开发者～\n给予开发者更多的动力～","copiedText":"已复制","paypalDonation":"👉 PayPal 捐款 (点击此处前往捐款页面)","wechatDonation":"👉 微信支付 (点击图片可查看收款码)","rotateMessageTop":"请旋转您的装置至横向","rotateMessageBottom":"建议使用电脑版本以获得最佳体验效果"},
            "zh-TW": {"title":"心動小鎮繪畫工具","step1":"選擇尺寸","step2":"上載圖片","step3":"生成繪畫","precision":"精細度","gridSize":"格數","next":"下一步","back":"返回","uploadText":"拖曳或點擊上傳圖片","loading":"正在生成繪畫...","selectLanguage":"選擇語言","infoTitle":"程式簡介及開發人員","levelSmall":"小","levelMedium":"中","levelLarge":"大","levelExtraLarge":"超大","showGrid":"顯示網格","hideGrid":"隱藏網格","eyedropper":"滴管工具","tutorial":"教程","currentColor":"當前顏色","zoom":"縮放","reset":"重置","save":"儲存","stepText":"步驟","toolsInfoTitle":"工具介紹","toolsInfoContent":"本工具專為《心動小鎮》玩家設計，能將上傳的圖片自動轉換為遊戲內繪畫適用的點陣圖格式，幫助玩家輕鬆創作像素風格畫作。開發者剛升級至遊戲14級時，深感在遊戲內繪畫的挑戰，因而開發此程式，希望幫助同樣喜愛繪畫的玩家更輕鬆地創作。","mainFeaturesTitle":"主要功能","mainFeaturesContent":"• 多種圖片比例：支援 16:9、4:3、1:1、3:4、9:16 等常用比例\n• 精細度選擇：四種等級設定，滿足不同繪畫需求\n• 遊戲原生色彩：僅使用遊戲內指定顏色進行轉換，確保作品風格一致\n• 繪畫輔助工具：內置網格顯示與滴管工具，便於對照與細節調整\n• 新手友好：提供分步教程，協助初學者快速上手","usageInstructionsTitle":"使用說明","usageInstructionsContent":"✅ 建議使用電腦版本以獲得最佳體驗效果。\n✅ 本工具所有圖片處理均在<b>本地進行</b>，不會上傳至任何伺服器，亦不使用任何AI程序，請放心使用個人相片。","developerInfoTitle":"關於開發者","developerInfoContent":"開發者是一名設計師兼遊戲開發者，也是《心動小鎮》的忠實玩家。歡迎來我的遊戲小島點讚！","gameIdLabel":"遊戲ID：","donationTitle":"❤️ 支持開發者","donationDesc":"如果覺得工具對你有幫助，歡迎打賞支持開發者～\n給予開發者更多的動力～","copiedText":"已複製","paypalDonation":"👉 PayPal 捐款 (點擊此處前往捐款頁面)","wechatDonation":"👉 微信支付 (點擊圖片可查看收款碼)","rotateMessageTop":"請旋轉您的裝置至橫向","rotateMessageBottom":"建議使用電腦版本以獲得最佳體驗效果"}
        };
        const i18n = {
            currentLang: 'zh-TW',
            translations: {},
            supportedLangs: ['zh-TW', 'zh-CN', 'en', 'th'],
            async init() {
                this.detectSystemLanguage();
                await this.loadTranslations();
                this.updatePage();
                this.setupEventListeners();
            },
            detectSystemLanguage() {
                const browserLang = navigator.language || navigator.userLanguage;
                const langMap = {
                    'zh-TW': 'zh-TW', 'zh-tw': 'zh-TW', 'zh-HK': 'zh-TW', 'zh-hk': 'zh-TW',
                    'zh-CN': 'zh-CN', 'zh-cn': 'zh-CN', 'zh': 'zh-CN',
                    'en': 'en', 'en-US': 'en', 'en-GB': 'en',
                    'th': 'th', 'th-th': 'th', 'th-TH': 'th'
                };
                const savedLang = localStorage.getItem('language');
                if (savedLang && this.supportedLangs.includes(savedLang)) {
                    this.currentLang = savedLang;
                    return;
                }
                for (const key in langMap) {
                    if (browserLang.startsWith(key)) {
                        this.currentLang = langMap[key];
                        localStorage.setItem('language', this.currentLang); return;
                    }
                }
                this.currentLang = 'en';
                localStorage.setItem('language', 'en');
            },
            async loadTranslations() {
                this.translations = localTranslations;
            },
            t(key, defaultText = key) {
                if (this.translations[this.currentLang] && this.translations[this.currentLang][key]) {
                    return this.translations[this.currentLang][key];
                }
                return defaultText;
            },
            setLanguage(lang) {
                if (this.supportedLangs.includes(lang)) {
                    this.currentLang = lang;
                    localStorage.setItem('language', lang);
                    this.updatePage();
                }
            },
            updatePage() {
                document.querySelectorAll('[data-i18n]').forEach(el => {
                    const key = el.getAttribute('data-i18n');
                    const text = this.t(key);
                    if (el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') {
                        el.placeholder = text; return;
                    }
                    if (typeof text === 'string' && text.indexOf('\n') !== -1) {
                        el.innerHTML = text.replace(/\n/g, '<br />');
                    } else {
                        el.textContent = text;
                    }
                });
                document.dispatchEvent(new CustomEvent('languageChanged', { detail: { language: this.currentLang } }));
                try { document.documentElement.setAttribute('lang', this.currentLang); } catch (err) {}
            },
            setupEventListeners() {
                const langBtn = document.getElementById('lang-btn');
                const langPopup = document.getElementById('lang-popup');
                const langOptions = document.querySelectorAll('.lang-option');
                const popupOverlay = document.querySelector('.popup-overlay');
                if (langBtn) langBtn.addEventListener('click', () => langPopup.classList.toggle('hidden'));
                langOptions.forEach(option => {
                    option.addEventListener('click', () => {
                        this.setLanguage(option.getAttribute('data-lang'));
                        langPopup.classList.add('hidden');
                    });
                });
                if (popupOverlay) popupOverlay.addEventListener('click', () => langPopup.classList.add('hidden'));
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => i18n.init());
        } else {
            i18n.init();
        }

        // ==========================================
        // JS - APP
        // ==========================================
        const app = {
            gridDimensions: {
                '16:9': [[30, 18], [50, 28], [100, 56], [150, 84]],
                '4:3': [[30, 24], [50, 38], [100, 76], [150, 114]],
                '1:1': [[30, 30], [50, 50], [100, 100], [150, 150]],
                '3:4': [[24, 30], [38, 50], [76, 100], [114, 150]],
                '9:16': [[18, 30], [28, 50], [56, 100], [84, 150]]
            },
            state: {
                currentStep: 1,
                selectedRatio: '16:9',
                selectedLevel: 0,
                uploadedImage: null,
                pixelArt: null,
                colors: {},
                currentColorGroup: 1,
                currentColor: '#051616'
            },
            init() {
                this.setupEventListeners();
                this.initializeColorLoader();
                this.updateGridDisplay();
            },
            setupEventListeners() {
                document.querySelectorAll('.size-item').forEach(item => {
                    item.addEventListener('click', () => this.selectSize(item));
                });
                document.querySelectorAll('.level-item').forEach(item => {
                    item.addEventListener('click', () => this.selectLevel(item));
                });
                const uploadArea = document.getElementById('upload-area');
                const imageInput = document.getElementById('image-input');
                uploadArea.addEventListener('click', () => imageInput.click());
                uploadArea.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadArea.style.borderColor = '#997C7B';
                    uploadArea.style.backgroundColor = '#FFF6EA';
                });
                uploadArea.addEventListener('dragleave', () => {
                    uploadArea.style.borderColor = '#FFE4BA';
                    uploadArea.style.backgroundColor = 'transparent';
                });
                uploadArea.addEventListener('drop', (e) => {
                    e.preventDefault();
                    uploadArea.style.borderColor = '#FFE4BA';
                    uploadArea.style.backgroundColor = 'transparent';
                    if (e.dataTransfer.files.length > 0) this.handleImageUpload(e.dataTransfer.files[0]);
                });
                imageInput.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) this.handleImageUpload(e.target.files[0]);
                });
                document.getElementById('info-btn').addEventListener('click', () => document.getElementById('info-popup').classList.remove('hidden'));
                document.querySelector('.btn-close').addEventListener('click', () => document.getElementById('info-popup').classList.add('hidden'));
                document.getElementById('info-popup').addEventListener('click', (e) => {
                    if (e.target.id === 'info-popup') document.getElementById('info-popup').classList.add('hidden');
                });
                document.getElementById('btn-home').addEventListener('click', () => window.location.reload());
                const wechatEl = document.getElementById('wechat-qr');
                if (wechatEl) {
                    wechatEl.style.cursor = 'pointer';
                    wechatEl.addEventListener('click', () => this.showWeChatPopup());
                }
            },
            async initializeColorLoader() {
                try {
                    const colorData = await colorLoader.initialize();
                    this.state.colors = colorData;
                    if (window.tools) tools.setupColorPanel();
                } catch (error) {}
            },
            selectSize(item) {
                document.querySelectorAll('.size-item').forEach(s => s.setAttribute('data-selected', 'false'));
                item.setAttribute('data-selected', 'true');
                this.state.selectedRatio = item.getAttribute('data-ratio');
                this.updateGridDisplay();
            },
            selectLevel(item) {
                document.querySelectorAll('.level-item').forEach(l => l.setAttribute('data-selected', 'false'));
                item.setAttribute('data-selected', 'true');
                this.state.selectedLevel = parseInt(item.getAttribute('data-level'));
                this.updateGridDisplay();
            },
            updateGridDisplay() {
                const dims = this.gridDimensions[this.state.selectedRatio][this.state.selectedLevel];
                document.getElementById('grid-display').textContent = `${dims[0]} x ${dims[1]}`;
            },
            handleImageUpload(file) {
                if (!file.type.startsWith('image/')) { alert('Please upload an image file'); return; }
                const reader = new FileReader();
                reader.onload = (e) => this.autoCropCenter(e.target.result);
                reader.readAsDataURL(file);
            },
            autoCropCenter(imageData) {
                const dims = this.gridDimensions[this.state.selectedRatio][this.state.selectedLevel];
                const gridW = dims[0], gridH = dims[1];
                const img = new Image();
                img.onload = () => {
                    const nw = img.naturalWidth, nh = img.naturalHeight;
                    const desiredAspect = gridW / gridH;
                    let sx, sy, sw, sh;
                    const imgAspect = nw / nh;
                    if (imgAspect > desiredAspect) {
                        sh = nh;
                        sw = Math.round(sh * desiredAspect);
                        sx = Math.round((nw - sw) / 2); sy = 0;
                    } else {
                        sw = nw;
                        sh = Math.round(sw / desiredAspect);
                        sx = 0; sy = Math.round((nh - sh) / 2);
                    }
                    const highCanvas = document.createElement('canvas');
                    highCanvas.width = sw; highCanvas.height = sh;
                    highCanvas.getContext('2d').drawImage(img, sx, sy, sw, sh, 0, 0, sw, sh);
                    const highResData = highCanvas.toDataURL('image/png');
                    const gridCanvas = document.createElement('canvas');
                    gridCanvas.width = gridW; gridCanvas.height = gridH;
                    gridCanvas.getContext('2d').drawImage(img, sx, sy, sw, sh, 0, 0, gridW, gridH);
                    const gridData = gridCanvas.toDataURL('image/png');
                    this.state.uploadedImage = gridData;
                    this.state.uploadedImagePreview = highResData;
                    this.displayImagePreview(highResData);
                    this.enableNextButton();
                };
                img.src = imageData;
            },
            displayImagePreview(imageData) {
                const uploadArea = document.getElementById('upload-area');
                const imagePreview = document.getElementById('image-preview');
                uploadArea.style.display = 'none';
                imagePreview.classList.remove('hidden');
                imagePreview.innerHTML = `<img src="${imageData}" alt="Preview">`;
                const imgInput = document.getElementById('image-input');
                if (imgInput) imgInput.value = '';
            },
            enableNextButton() { document.getElementById('btn-step2-next').disabled = false; },
            goToStep(step) {
                document.querySelectorAll('.step').forEach(s => s.classList.add('hidden'));
                const stepElement = document.getElementById(`step-${step}`);
                if (stepElement) {
                    // Reset animation sequence
                    stepElement.style.animation = 'none';
                    stepElement.offsetHeight; // trigger reflow
                    stepElement.style.animation = null; 
                    stepElement.classList.remove('hidden');
                }
                this.state.currentStep = step;
            },
            nextStep() {
                switch (this.state.currentStep) {
                    case 1: this.goToStep(2); break;
                    case 2:
                        if (this.state.uploadedImage) {
                            this.goToStep(3);
                            this.processImage();
                        }
                        break;
                    case 3:
                        setTimeout(() => {
                            this.goToStep(4);
                            this.initializePainting();
                        }, 1500);
                        break;
                }
            },
            processImage() {
                setTimeout(() => this.nextStep(), 2000);
            },
            initializePainting() {
                if (window.canvas) window.canvas.initialize(this.state.selectedRatio, this.state.selectedLevel, this.state.uploadedImage);
                if (window.colorPanel) window.colorPanel.initialize(this.state.colors);
                if (window.tools) window.tools.initialize();
            },
            resetState() {
                this.state = {
                    currentStep: 1, selectedRatio: '16:9', selectedLevel: 0,
                    uploadedImage: null, pixelArt: null, colors: this.state.colors,
                    currentColorGroup: 1, currentColor: '#051616'
                };
                document.getElementById('upload-area').style.display = 'flex';
                document.getElementById('image-preview').classList.add('hidden');
                document.getElementById('btn-step2-next').disabled = true;
                document.querySelectorAll('.size-item').forEach(item => item.setAttribute('data-selected', item.getAttribute('data-ratio') === '16:9' ? 'true' : 'false'));
                document.querySelectorAll('.level-item').forEach(item => item.setAttribute('data-selected', item.getAttribute('data-level') === '0' ? 'true' : 'false'));
                this.updateGridDisplay();
                const imgInput = document.getElementById('image-input');
                if (imgInput) imgInput.value = '';
            },
            showWeChatPopup() {
                const existing = document.getElementById('wechat-popup');
                if (existing) existing.remove();
                const modal = document.createElement('div');
                modal.id = 'wechat-popup';
                Object.assign(modal.style, { position: 'fixed', inset: '0', background: 'rgba(0,0,0,0.6)', backdropFilter: 'blur(5px)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 99999, transition: 'all 0.3s' });
                const panel = document.createElement('div');
                Object.assign(panel.style, { position: 'relative', background: '#fff', padding: '12px', borderRadius: '12px', maxWidth: '90%', maxHeight: '90%', boxSizing: 'border-box', boxShadow: '0 15px 40px rgba(0,0,0,0.3)', animation: 'popupIn 0.3s ease' });
                const img = document.createElement('img');
                img.src = 'https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/wechat_pay.jpg';
                Object.assign(img.style, { maxWidth: '400px', width: '100%', height: 'auto', display: 'block', borderRadius: '8px' });
                const btn = document.createElement('button');
                btn.className = 'btn-close';
                const closeImg = document.createElement('img');
                closeImg.src = 'https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_btn_close.svg';
                btn.appendChild(closeImg);
                btn.addEventListener('click', () => modal.remove());
                modal.addEventListener('click', (e) => { if (e.target === modal) modal.remove(); });
                panel.appendChild(img);
                panel.appendChild(btn);
                modal.appendChild(panel);
                document.body.appendChild(modal);
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            if (typeof colorLoader !== 'undefined') app.init();
        });
        const gameIdElement = document.querySelector('#game-id');
        gameIdElement.addEventListener('click', function() {
            const text = this.textContent;
            navigator.clipboard.writeText(text).then(() => {
                const originalText = this.textContent;
                this.textContent = $(".copied_text").html();
                setTimeout(() => this.textContent = originalText, 1500);
            });
        });
        gameIdElement.style.cursor = 'pointer';
        gameIdElement.title = '點擊複製遊戲ID';
        // ==========================================
        // JS - CANVAS
        // ==========================================
        const canvas = {
            properties: {
                element: null, context: null, ratio: '16:9', level: 0,
                gridWidth: 30, gridHeight: 18, pixelSize: 20, offsetX: 0, offsetY: 0,
                zoom: 1.0, isDragging: false, hasDragged: false, dragStartX: 0, dragStartY: 0,
                highlightGridPos: null, canvasWidth: 1100, canvasHeight: 630
            },
            pixelGrid: [],
            gridDimensions: {
                '16:9': [[30, 18], [50, 28], [100, 56], [150, 84]],
                '4:3': [[30, 24], [50, 38], [100, 76], [150, 114]],
                '1:1': [[30, 30], [50, 50], [100, 100], [150, 150]],
                '3:4': [[24, 30], [38, 50], [76, 100], [114, 150]],
                '9:16': [[18, 30], [28, 50], [56, 100], [84, 150]]
            },
            initialize(ratio, level, imageData) {
                this.properties.element = document.getElementById('paint-canvas');
                this.properties.context = this.properties.element.getContext('2d', { willReadFrequently: true });
                this.properties.ratio = ratio;
                this.properties.level = level;
                const dims = this.gridDimensions[ratio][level];
                this.properties.gridWidth = dims[0];
                this.properties.gridHeight = dims[1];
                const canvasWidth = 1100, canvasHeight = 630;
                this.properties.pixelSize = Math.min(canvasWidth / this.properties.gridWidth, canvasHeight / this.properties.gridHeight);
                this.properties.canvasWidth = canvasWidth;
                this.properties.canvasHeight = canvasHeight;
                this.properties.element.width = canvasWidth;
                this.properties.element.height = canvasHeight;
                this.initializePixelGrid();
                this.centerImage();
                this.processImage(imageData);
                this.setupEventListeners();
                this.draw();
            },
            centerImage() {
                const gridPixelWidth = this.properties.pixelSize * this.properties.gridWidth;
                const gridPixelHeight = this.properties.pixelSize * this.properties.gridHeight;
                this.properties.offsetX = (this.properties.canvasWidth - gridPixelWidth) / 2;
                this.properties.offsetY = (this.properties.canvasHeight - gridPixelHeight) / 2;
            },
            initializePixelGrid() {
                this.pixelGrid = [];
                for (let y = 0; y < this.properties.gridHeight; y++) {
                    this.pixelGrid[y] = [];
                    for (let x = 0; x < this.properties.gridWidth; x++) this.pixelGrid[y][x] = '#FFFFFF';
                }
            },
            processImage(imageData) {
                const img = new Image();
                img.onload = () => {
                    const tempCanvas = document.createElement('canvas');
                    tempCanvas.width = this.properties.gridWidth; tempCanvas.height = this.properties.gridHeight;
                    const tempCtx = tempCanvas.getContext('2d');
                    tempCtx.drawImage(img, 0, 0, this.properties.gridWidth, this.properties.gridHeight);
                    const data = tempCtx.getImageData(0, 0, this.properties.gridWidth, this.properties.gridHeight).data;
                    const allColors = this.getAllAvailableColors(app.state.colors);
                    for (let i = 0; i < data.length; i += 4) {
                        const closestColor = this.findClosestColor(data[i], data[i+1], data[i+2], allColors);
                        const pixelIndex = i / 4;
                        const y = Math.floor(pixelIndex / this.properties.gridWidth);
                        const x = pixelIndex % this.properties.gridWidth;
                        if (y < this.properties.gridHeight) this.pixelGrid[y][x] = closestColor;
                    }
                    this.draw();
                };
                img.src = imageData;
            },
            getAllAvailableColors(colorGroups) {
                const allColors = [];
                Object.values(colorGroups).forEach(group => { if (group.colors) allColors.push(...group.colors); });
                return allColors;
            },
            findClosestColor(r, g, b, colorPalette) {
                let closestColor = colorPalette[0], minDistance = Infinity;
                colorPalette.forEach(hex => {
                    const [pr, pg, pb] = this.hexToRgb(hex);
                    const distance = Math.sqrt(Math.pow(r - pr, 2) + Math.pow(g - pg, 2) + Math.pow(b - pb, 2));
                    if (distance < minDistance) { minDistance = distance; closestColor = hex; }
                });
                return closestColor;
            },
            hexToRgb(hex) {
                const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
                return result ? [parseInt(result[1], 16), parseInt(result[2], 16), parseInt(result[3], 16)] : [255, 255, 255];
            },
            setupEventListeners() {
                const element = this.properties.element;
                element.addEventListener('mousedown', (e) => this.handleMouseDown(e));
                element.addEventListener('mousemove', (e) => this.handleMouseMove(e));
                element.addEventListener('mouseup', (e) => this.handleMouseUp(e));
                element.addEventListener('wheel', (e) => this.handleWheel(e));
                element.addEventListener('contextmenu', (e) => e.preventDefault());
                element.addEventListener('touchstart', (e) => this.handleTouchStart(e), { passive: false });
                element.addEventListener('touchmove', (e) => this.handleTouchMove(e), { passive: false });
                element.addEventListener('touchend', (e) => this.handleTouchEnd(e), { passive: false });
                document.getElementById('zoom-slider').addEventListener('input', (e) => {
                    this.setZoom(parseFloat(e.target.value));
                    this.draw();
                });
            },
            handleMouseDown(e) {
                const pos = this.getCanvasCoords(e.clientX, e.clientY);
                if (e.button === 0) {
                    this.properties.isDragging = true;
                    this.properties.hasDragged = false;
                    this.properties.dragStartX = pos.x; 
                    this.properties.dragStartY = pos.y;
                    this.properties.element.style.cursor = 'grabbing';
                }
            },
            handleMouseMove(e) {
                const pos = this.getCanvasCoords(e.clientX, e.clientY);
                if (this.properties.isDragging) {
                    const dx = pos.x - this.properties.dragStartX;
                    const dy = pos.y - this.properties.dragStartY;
                    
                    if (Math.abs(dx) > 2 || Math.abs(dy) > 2) {
                        this.properties.hasDragged = true;
                    }
                    
                    this.properties.offsetX += dx;
                    this.properties.offsetY += dy;
                    this.properties.dragStartX = pos.x; 
                    this.properties.dragStartY = pos.y;
                    this.clampOffsets();
                    this.draw();
                }

                if (window.tools && window.tools.eyedropperActive) {
                    this.properties.highlightGridPos = this.getGridPosition(pos.x, pos.y);
                    if (!this.properties.isDragging) {
                        this.properties.element.style.cursor = 'crosshair';
                    }
                    if (!this.properties.isDragging || this.properties.hasDragged) {
                        this.draw();
                    }
                } else if (!this.properties.isDragging) {
                    this.properties.element.style.cursor = 'grab';
                }
            },
            handleMouseUp(e) {
                if (e.button === 0) {
                    if (window.tools && window.tools.eyedropperActive && !this.properties.hasDragged) {
                        const pos = this.getCanvasCoords(e.clientX, e.clientY);
                        const gridPos = this.getGridPosition(pos.x, pos.y);
                        if (gridPos) {
                            this.pickColor(gridPos.x, gridPos.y);
                        }
                    }

                    this.properties.isDragging = false;
                    this.properties.hasDragged = false;
                    
                    if (window.tools && window.tools.eyedropperActive) {
                        this.properties.element.style.cursor = 'crosshair';
                    } else {
                        this.properties.element.style.cursor = 'grab';
                    }
                }
            },
            handleWheel(e) {
                e.preventDefault();
                const delta = e.deltaY > 0 ? -0.1 : 0.1;
                this.setZoom(Math.max(1, Math.min(10, this.properties.zoom + delta)));
                this.draw();
            },
            handleTouchStart(e) {
                if (!e.touches || e.touches.length === 0) return;
                const pos = this.getCanvasCoords(e.touches[0].clientX, e.touches[0].clientY);
                if (e.touches.length === 1) {
                    e.preventDefault();
                    this.properties.isDragging = true;
                    this.properties.hasDragged = false;
                    this.properties.dragStartX = pos.x; 
                    this.properties.dragStartY = pos.y;
                    this.properties.element.style.cursor = 'grabbing';
                }
            },
            handleTouchMove(e) {
                if (!e.touches || e.touches.length === 0) return;
                if (e.touches.length === 2) { e.preventDefault(); }
                else if (e.touches.length === 1) {
                    e.preventDefault();
                    const pos = this.getCanvasCoords(e.touches[0].clientX, e.touches[0].clientY);
                    
                    if (this.properties.isDragging) {
                        const dx = pos.x - this.properties.dragStartX;
                        const dy = pos.y - this.properties.dragStartY;
                        if (Math.abs(dx) > 2 || Math.abs(dy) > 2) {
                            this.properties.hasDragged = true;
                        }
                        this.properties.offsetX += dx;
                        this.properties.offsetY += dy;
                        this.properties.dragStartX = pos.x; 
                        this.properties.dragStartY = pos.y;
                        this.clampOffsets(); 
                        this.draw();
                    }
                    
                    if (window.tools && window.tools.eyedropperActive) {
                        this.properties.highlightGridPos = this.getGridPosition(pos.x, pos.y);
                        if (!this.properties.isDragging || this.properties.hasDragged) {
                            this.draw();
                        }
                    }
                }
            },
            handleTouchEnd(e) {
                if (this.properties.isDragging) {
                    if (window.tools && window.tools.eyedropperActive && !this.properties.hasDragged) {
                        if (e.changedTouches && e.changedTouches.length > 0) {
                            const pos = this.getCanvasCoords(e.changedTouches[0].clientX, e.changedTouches[0].clientY);
                            const gridPos = this.getGridPosition(pos.x, pos.y);
                            if (gridPos) {
                                this.pickColor(gridPos.x, gridPos.y);
                            }
                        }
                    }
                    this.properties.isDragging = false;
                    this.properties.hasDragged = false;
                    if (window.tools && !window.tools.eyedropperActive) {
                        this.properties.element.style.cursor = 'grab';
                    }
                }
                if (!window.tools || !window.tools.eyedropperActive) {
                    this.properties.highlightGridPos = null;
                    this.draw();
                }
            },
            getGridPosition(x, y) {
                const pixelSize = this.properties.pixelSize * this.properties.zoom;
                const gridX = Math.floor((x - this.properties.offsetX) / pixelSize);
                const gridY = Math.floor((y - this.properties.offsetY) / pixelSize);
                if (gridX >= 0 && gridX < this.properties.gridWidth && gridY >= 0 && gridY < this.properties.gridHeight) {
                    return { x: gridX, y: gridY };
                }
                return null;
            },
            getCanvasCoords(clientX, clientY) {
                const rect = this.properties.element.getBoundingClientRect();
                return {
                    x: (clientX - rect.left) * (this.properties.element.width / rect.width),
                    y: (clientY - rect.top) * (this.properties.element.height / rect.height)
                };
            },
            setZoom(newZoom, clientX, clientY) {
                if (newZoom === this.properties.zoom) return;
                const oldPixelSize = this.properties.pixelSize * this.properties.zoom;
                const newPixelSize = this.properties.pixelSize * newZoom;
                let pivotX = this.properties.element.width / 2, pivotY = this.properties.element.height / 2;
                if (typeof clientX === 'number' && typeof clientY === 'number') {
                    const p = this.getCanvasCoords(clientX, clientY);
                    pivotX = p.x; pivotY = p.y;
                }
                this.properties.offsetX = pivotX - (pivotX - this.properties.offsetX) * (newPixelSize / oldPixelSize);
                this.properties.offsetY = pivotY - (pivotY - this.properties.offsetY) * (newPixelSize / oldPixelSize);
                this.properties.zoom = newZoom;
                const slider = document.getElementById('zoom-slider');
                if (slider) slider.value = newZoom;
                const display = document.getElementById('zoom-display');
                if (display) display.textContent = newZoom.toFixed(1) + 'x';
                this.clampOffsets();
            },
            clampOffsets() {
                const gridPixelWidth = this.properties.pixelSize * this.properties.zoom * this.properties.gridWidth;
                const gridPixelHeight = this.properties.pixelSize * this.properties.zoom * this.properties.gridHeight;
                if (gridPixelWidth <= this.properties.canvasWidth) {
                    this.properties.offsetX = (this.properties.canvasWidth - gridPixelWidth) / 2;
                } else {
                    this.properties.offsetX = Math.min(0, Math.max(this.properties.canvasWidth - gridPixelWidth, this.properties.offsetX));
                }
                if (gridPixelHeight <= this.properties.canvasHeight) {
                    this.properties.offsetY = (this.properties.canvasHeight - gridPixelHeight) / 2;
                } else {
                    this.properties.offsetY = Math.min(0, Math.max(this.properties.canvasHeight - gridPixelHeight, this.properties.offsetY));
                }
            },
            pickColor(x, y) {
                const color = this.pixelGrid[y][x];
                app.state.currentColor = color;
                document.dispatchEvent(new CustomEvent('colorPicked', { detail: { color: color } }));
            },
            draw() {
                const ctx = this.properties.context;
                const pixelSize = this.properties.pixelSize * this.properties.zoom;
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, this.properties.element.width, this.properties.element.height);
                ctx.save();
                ctx.translate(this.properties.offsetX, this.properties.offsetY);
                for (let y = 0; y < this.properties.gridHeight; y++) {
                    for (let x = 0; x < this.properties.gridWidth; x++) {
                        let pixelColor = this.pixelGrid[y][x] || '#FFFFFF';
                        if (window.tools && window.tools.tutorialActive && window.tools.tutorialData.length > 0) {
                            const highlightColor = (window.tools.tutorialData[window.tools.currentTutorialStep] || '').toLowerCase();
                            ctx.fillStyle = pixelColor.toLowerCase() === highlightColor ? pixelColor : '#F3F3F3';
                        } else {
                            ctx.fillStyle = pixelColor;
                        }
                        ctx.fillRect(x * pixelSize, y * pixelSize, pixelSize, pixelSize);
                        if (window.tools && window.tools.gridVisible) this.drawGridLine(ctx, x, y, pixelSize);
                    }
                }

                if (window.tools && window.tools.gridVisible) {
                    ctx.lineWidth = 3;
                    ctx.strokeStyle = '#999999';
                    const gridW = pixelSize * this.properties.gridWidth, gridH = pixelSize * this.properties.gridHeight;
                    for (let c = 5; c < this.properties.gridWidth; c += 5) {
                        ctx.beginPath();
                        ctx.moveTo(c * pixelSize, 0); ctx.lineTo(c * pixelSize, gridH); ctx.stroke();
                    }
                    for (let k = 1; ; k++) {
                        const boundaryRow = this.properties.gridHeight - k * 5;
                        if (boundaryRow <= 0) break;
                        ctx.beginPath(); ctx.moveTo(0, boundaryRow * pixelSize); ctx.lineTo(gridW, boundaryRow * pixelSize); ctx.stroke();
                    }
                }

                if (window.tools && window.tools.eyedropperActive && this.properties.highlightGridPos) {
                    const pos = this.properties.highlightGridPos;
                    ctx.strokeStyle = '#FFFFFF'; ctx.lineWidth = 4;
                    ctx.strokeRect(pos.x * pixelSize, pos.y * pixelSize, pixelSize, pixelSize);
                    ctx.strokeStyle = '#000000'; ctx.lineWidth = 2;
                    ctx.strokeRect(pos.x * pixelSize - 2, pos.y * pixelSize - 2, pixelSize + 4, pixelSize + 4);
                    ctx.strokeStyle = '#FFFF00';
                    ctx.lineWidth = 1;
                    ctx.strokeRect(pos.x * pixelSize + 1, pos.y * pixelSize + 1, pixelSize - 2, pixelSize - 2);
                }
                ctx.restore();
            },
            drawGridLine(ctx, x, y, pixelSize) {
                ctx.strokeStyle = '#999999';
                ctx.lineWidth = 1;
                ctx.strokeRect(x * pixelSize, y * pixelSize, pixelSize, pixelSize);
                if ((x + 1) % 5 === 0) {
                    ctx.lineWidth = 2;
                    ctx.beginPath(); ctx.moveTo((x + 1) * pixelSize, y * pixelSize); ctx.lineTo((x + 1) * pixelSize, (y + 1) * pixelSize); ctx.stroke();
                }
                if ((y + 1) % 5 === 0) {
                    ctx.lineWidth = 2;
                    ctx.beginPath(); ctx.moveTo(x * pixelSize, (y + 1) * pixelSize); ctx.lineTo((x + 1) * pixelSize, (y + 1) * pixelSize); ctx.stroke();
                }
            }
        };
        window.canvas = canvas;

        // ==========================================
        // JS - TOOLS
        // ==========================================
        const tools = {
            gridVisible: false, eyedropperActive: false, tutorialActive: false,
            tutorialData: [], currentTutorialStep: 0,
            initialize() {
                this.setupToolButtons();
                this.setupTutorial();
                this.ensurePickerSafety();
                try { localStorage.setItem('tutorial_last_step', 0); } catch (err) {}
            },
            ensurePickerSafety() {
                this.eyedropperActive = false;
                const c = document.getElementById('paint-canvas');
                if (c) c.style.cursor = 'grab';
                const resetIfNotActive = () => {
                    const pickBtn = document.getElementById('tool-pick');
                    if (!pickBtn || !pickBtn.classList.contains('active')) {
                        this.eyedropperActive = false;
                        if (c) c.style.cursor = 'grab';
                        if (pickBtn) pickBtn.classList.remove('active');
                        try { if (pickBtn && pickBtn.querySelector('svg')) pickBtn.querySelector('svg').style.fill = '#713F3E';
                        } catch (e) {}
                    }
                };
                document.addEventListener('touchstart', resetIfNotActive, { passive: true });
                document.addEventListener('pointerdown', resetIfNotActive, { passive: true });
                document.addEventListener('visibilitychange', () => { if (!document.hidden) resetIfNotActive(); });
            },
            setupToolButtons() {
                document.getElementById('tool-grid').addEventListener('click', () => this.toggleGrid());
                document.getElementById('tool-pick').addEventListener('click', () => this.toggleEyedropper());
                document.getElementById('tool-teach').addEventListener('click', () => this.toggleTutorial());
            },
            toggleGrid() {
                this.gridVisible = !this.gridVisible;
                const btn = document.getElementById('tool-grid');
                if (this.gridVisible) {
                    btn.classList.add('active');
                    btn.querySelector('svg').style.fill = '#FFFFFF';
                } else {
                    btn.classList.remove('active');
                    btn.querySelector('svg').style.fill = '#713F3E';
                }
                if (window.canvas) window.canvas.draw();
            },
            toggleEyedropper() {
                this.eyedropperActive = !this.eyedropperActive;
                const btn = document.getElementById('tool-pick');
                const c = document.getElementById('paint-canvas');
                if (this.eyedropperActive) {
                    btn.classList.add('active');
                    btn.querySelector('svg').style.fill = '#FFFFFF'; c.style.cursor = 'crosshair';
                } else {
                    btn.classList.remove('active');
                    btn.querySelector('svg').style.fill = '#713F3E'; c.style.cursor = 'grab';
                }
            },
            toggleTutorial() {
                this.tutorialActive = !this.tutorialActive;
                const btn = document.getElementById('tool-teach');
                const pnl = document.getElementById('tutorial-panel');
                if (this.tutorialActive) {
                    btn.classList.add('active');
                    btn.querySelector('svg').style.fill = '#FFFFFF';
                    pnl.classList.remove('hidden'); this.initializeTutorial();
                } else {
                    btn.classList.remove('active');
                    btn.querySelector('svg').style.fill = '#713F3E';
                    pnl.classList.add('hidden'); this.completeTutorial();
                }
            },
            setupColorPanel() {
                const colorGroups = app.state.colors;
                const groupsDisplay = document.getElementById('color-groups-display');
                groupsDisplay.innerHTML = '';
                let groupIndex = 0;
                Object.entries(colorGroups).forEach(([groupName, groupData]) => {
                    const groupItem = document.createElement('div');
                    groupItem.className = 'color-group-item';
                    groupItem.style.backgroundColor = groupData.mainColor;
                    groupItem.setAttribute('data-group-index', groupIndex);
                    groupsDisplay.appendChild(groupItem);
                    groupIndex++;
                });
                const initOwlCarousel = () => {
                    if (typeof $ !== 'undefined' && typeof $.fn.owlCarousel !== 'undefined') {
                        $(groupsDisplay).owlCarousel({
                            items: 5, center: true, loop: false, dots: false, margin: 0,
                            onChanged: (event) => {
                                setTimeout(() => {
                                    const centerItem = groupsDisplay.querySelector('.center');
                                    if (centerItem) {
                                        const groupItem = centerItem.querySelector('.color-group-item');
                                        if (groupItem) this.selectColorGroup(parseInt(groupItem.getAttribute('data-group-index')));
                                    }
                                }, 100);
                            }
                        });
                        this.carouselElement = groupsDisplay;
                    } else { setTimeout(initOwlCarousel, 100); }
                };
                initOwlCarousel();
                this.selectColorGroup(0);
                const centerItem = document.createElement('div');
                centerItem.className = 'color-group-cennter-line';
                groupsDisplay.appendChild(centerItem);
            },
            navigateToColorGroup(index) {
                if (this.carouselElement && typeof $ !== 'undefined') $(this.carouselElement).trigger('to.owl.carousel', [index, 300]);
                this.selectColorGroup(index);
            },
            selectColorGroup(index) {
                if (!app.state.colors) return;
                app.state.currentColorGroup = index;
                document.querySelectorAll('.color-group-item').forEach((item, i) => item.classList.toggle('selected', i === index));
                const groupNames = Object.keys(app.state.colors);
                if (groupNames.length === 0 || index >= groupNames.length) return;
                const groupData = app.state.colors[groupNames[index]];
                if (groupData && groupData.colors) this.displayGroupColors(groupData.colors);
            },
            displayGroupColors(colors) {
                const detailsContainer = document.getElementById('color-details');
                detailsContainer.innerHTML = '';
                colors.forEach((color, index) => {
                    const colorItem = document.createElement('div');
                    colorItem.className = 'color-detail-item color-display';
                    colorItem.id = `color-detail-item-${index}`;
                    colorItem.setAttribute('data-color-index', index);
                    colorItem.addEventListener('click', () => { app.state.currentColor = color; this.updateColorSelection(); });
                    detailsContainer.appendChild(colorItem);
                    if (!colorItem.querySelector('svg') && colorItem.dataset.svgLoading !== 'true' && colorItem.dataset.svgInitialized !== 'true') {
                        colorItem.dataset.svgLoading = 'true';
                        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_color.svg', `color-detail-item-${index}`);
                    }
                    this.updateColorSVG(index, color);
                });
            },
            updateColorSelection() {
                document.querySelectorAll('#color_icon_select').forEach(el => { el.style.stroke = 'none'; el.style.fill = 'none'; });
                const currentColor = app.state.currentColor;
                document.querySelectorAll('.color-detail-item').forEach(item => {
                    if (item.style.backgroundColor.toLowerCase() === currentColor.toLowerCase()) {
                        const select = item.querySelector('#color_icon_select');
                        if (select) { select.style.stroke = '#FFFFFF'; select.style.fill = 'none'; }
                    }
                });
                const tutorialColorFill = document.getElementById('color_icon_fill');
                if (tutorialColorFill) tutorialColorFill.setAttribute('fill', currentColor);
            },
            updateColorSVG(containerIndex, color) {
                const maxAttempts = 40, delay = 50;
                let attempts = 0;
                const tryUpdate = () => {
                    attempts++;
                    const container = document.getElementById(`color-detail-item-${containerIndex}`);
                    if (!container) return (attempts < maxAttempts) ? setTimeout(tryUpdate, delay) : undefined;
                    const svg = container.querySelector('svg');
                    if (!svg) return (attempts < maxAttempts) ? setTimeout(tryUpdate, delay) : undefined;
                    try {
                        container.dataset.svgInitialized = 'true';
                        if (container.dataset.svgLoading) delete container.dataset.svgLoading;
                        let fillPath = null, bgPath = null;
                        const group11 = svg.querySelector('g[data-name="Group 11"]');
                        if (group11) {
                            const paths = group11.querySelectorAll('path');
                            if (paths.length >= 2) { bgPath = paths[0]; fillPath = paths[1];
                            }
                        }
                        if (!fillPath) {
                            const allPaths = svg.querySelectorAll('path');
                            if (allPaths.length >= 2) { bgPath = allPaths[0]; fillPath = allPaths[1];
                            }
                            else if (allPaths.length === 1) { fillPath = allPaths[0];
                            }
                        }
                        if (fillPath) fillPath.setAttribute('fill', color);
                        const isSelected = (color || '').toLowerCase() === (app.state.currentColor || '').toLowerCase();
                        if (bgPath) bgPath.setAttribute('fill', isSelected ? '#FFFFFF' : 'transparent');
                    } catch (err) { if (attempts < maxAttempts) return setTimeout(tryUpdate, delay);
                    }
                };
                tryUpdate();
            },
            setupTutorial() {
                if (window.canvas && window.canvas.pixelGrid) this.generateTutorialSteps();
                document.getElementById('tut-first').addEventListener('click', () => this.goToTutorialStep(0));
                document.getElementById('tut-prev').addEventListener('click', () => this.goToTutorialStep(Math.max(0, this.currentTutorialStep - 1)));
                document.getElementById('tut-next').addEventListener('click', () => this.goToTutorialStep(Math.min(this.tutorialData.length - 1, this.currentTutorialStep + 1)));
                document.getElementById('tut-last').addEventListener('click', () => this.goToTutorialStep(this.tutorialData.length - 1));
            },
            generateTutorialSteps() {
                const colorCounts = {};
                if (window.canvas && window.canvas.pixelGrid) {
                    window.canvas.pixelGrid.forEach(row => {
                        row.forEach(color => { if (color !== '#FFFFFF') colorCounts[color] = (colorCounts[color] || 0) + 1; });
                    });
                }
                this.tutorialData = Object.entries(colorCounts).sort((a, b) => b[1] - a[1]).map(entry => entry[0]);
                this.currentTutorialStep = 0;
            },
            goToTutorialStep(step) {
                this.currentTutorialStep = Math.max(0, Math.min(step, this.tutorialData.length - 1));
                this.showTutorialStep(this.currentTutorialStep);
            },
            showTutorialStep(step) {
                if (step < 0 || step >= this.tutorialData.length) return;
                const color = this.tutorialData[step];
                try { localStorage.setItem('tutorial_last_step', step); } catch (err) {}
                document.getElementById('tut-step-num').textContent = step + 1;
                const colorDisplayBg = document.querySelector('#tut-color.color-display svg g[data-name="Group 11"] > path:nth-child(1)');
                if (colorDisplayBg) colorDisplayBg.setAttribute('fill', '#FFFFFF');
                const colorDisplay = document.querySelector('#tut-color.color-display svg g[data-name="Group 11"] > path:nth-child(2)');
                if (colorDisplay) colorDisplay.setAttribute('fill', color);
                app.state.currentColor = color;
                const colorGroups = app.state.colors || {}; let foundGroupIndex = -1;
                Object.entries(colorGroups).forEach(([groupName, groupData], index) => {
                    if (groupData.colors && groupData.colors.includes(color)) foundGroupIndex = index;
                });
                if (foundGroupIndex !== -1) this.navigateToColorGroup(foundGroupIndex);
                this.updateColorSelection();

                const firstBtn = document.getElementById('tut-first'), prevBtn = document.getElementById('tut-prev');
                const nextBtn = document.getElementById('tut-next'), lastBtn = document.getElementById('tut-last');
                if (firstBtn) { firstBtn.disabled = (step === 0); firstBtn.style.opacity = (step === 0) ? '0.5' : '1';
                }
                if (prevBtn) { prevBtn.disabled = (step === 0);
                prevBtn.style.opacity = (step === 0) ? '0.5' : '1'; }
                const lastIndex = this.tutorialData.length - 1;
                if (nextBtn) { nextBtn.disabled = (step === lastIndex); nextBtn.style.opacity = (step === lastIndex) ? '0.5' : '1';
                }
                if (lastBtn) { lastBtn.disabled = (step === lastIndex);
                lastBtn.style.opacity = (step === lastIndex) ? '0.5' : '1'; }
                this.currentTutorialStep = step;
                if (window.canvas) window.canvas.draw();
            },
            initializeTutorial() {
                if (!this.tutorialData || this.tutorialData.length === 0) this.generateTutorialSteps();
                if (this.tutorialData.length === 0) return;
                const saved = localStorage.getItem('tutorial_last_step'); let step = 0;
                if (saved !== null) {
                    const idx = parseInt(saved, 10);
                    if (!isNaN(idx) && idx >= 0 && idx < this.tutorialData.length) step = idx;
                }
                this.goToTutorialStep(step);
            },
            completeTutorial() {
                this.currentTutorialStep = 0;
                try { localStorage.setItem('tutorial_last_step', this.currentTutorialStep); } catch (err) {}
                if (window.canvas && window.canvas.pixelGrid) window.canvas.draw();
            }
        };
        window.tools = tools;
        document.addEventListener('colorPicked', (e) => {
            const pickedColor = e.detail.color;
            app.state.currentColor = pickedColor;
            const colorGroups = app.state.colors; let foundGroupIndex = -1;
            Object.entries(colorGroups).forEach(([groupName, groupData], index) => {
                if (groupData.colors && groupData.colors.includes(pickedColor)) foundGroupIndex = index;
            });
            if (foundGroupIndex !== -1) tools.navigateToColorGroup(foundGroupIndex);
            tools.updateColorSelection();
        });
        // ==========================================
        // JS - LOAD SVG
        // ==========================================
        async function embedExternalSVG(url, containerId) {
            try {
                const response = await fetch(url);
                const svgText = await response.text();
                const parser = new DOMParser();
                const svgDoc = parser.parseFromString(svgText, 'image/svg+xml');
                const svgElement = svgDoc.documentElement;
                const prefix = `svg-${Math.random().toString(36).slice(2, 8)}`;
                const idMap = new Map();
                svgElement.querySelectorAll('[id]').forEach(el => {
                    const oldId = el.getAttribute('id'); const newId = `${prefix}-${oldId}`;
                    idMap.set(oldId, newId); el.setAttribute('id', newId);
                });
                svgElement.querySelectorAll('*').forEach(el => {
                    for (let i = 0; i < el.attributes.length; i++) {
                        let attr = el.attributes[i]; let value = attr.value;
                        value = value.replace(/url\(#([^)]+)\)/g, (match, oldId) => {
                            const newId = idMap.get(oldId); return newId ? `url(#${newId})` : match;
                        });
                        value = value.replace(/#([^ ,;]+)/g, (match, oldId) => {
                            const newId = idMap.get(oldId); return newId ? `#${newId}` : match;
                        });
                        if (value !== attr.value) el.setAttribute(attr.name, value);
                    }
                });
                document.getElementById(containerId).appendChild(svgElement);
            } catch (error) { console.error('Error loading or modifying SVG:', error);
            }
        }

        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_loading.svg', 'loading-icon');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_grid.svg', 'tool-grid');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_pick.svg', 'tool-pick');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_teach.svg', 'tool-teach');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_color.svg', 'color-svg');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_btn_first_page.svg', 'tut-first');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_btn_prev_page.svg', 'tut-prev');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_btn_next_page.svg', 'tut-next');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_btn_last_page.svg', 'tut-last');
        embedExternalSVG('https://cdn.jsdelivr.net/gh/zerochansy/Heartopia-Painting-Tools@main/assets/images/icon_color.svg', 'tut-color');
    </script>
</body>
</html>
