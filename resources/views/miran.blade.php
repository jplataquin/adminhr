<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Miran Puzzle Game</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <style>
        :root {
            --tile-size: 100px;
            --neon-primary: #00f2ff;
            --neon-secondary: #0062ff;
        }
        @media (min-width: 576px) {
            :root {
                --tile-size: 150px;
            }
        }
        @media (min-width: 768px) {
            :root {
                --tile-size: 200px;
            }
        }

        .puzzle-board {
            display: grid;
            grid-template-columns: repeat(3, var(--tile-size));
            grid-template-rows: repeat(3, var(--tile-size));
            gap: 2px;
            width: calc(3 * var(--tile-size) + 4px);
            margin: 0 auto;
            background-color: #444;
            border: 4px solid #444;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            touch-action: none; /* Prevent scrolling/zooming while playing */
            transition: all 0.5s ease;
        }

        .puzzle-board.solved {
            border: 4px solid var(--neon-primary);
            box-shadow: 0 0 20px var(--neon-primary), 0 0 40px var(--neon-secondary);
            animation: neon-pulse 1.5s infinite alternate;
        }

        @keyframes neon-pulse {
            from {
                border-color: var(--neon-primary);
                box-shadow: 0 0 10px var(--neon-primary), 0 0 20px var(--neon-secondary);
            }
            to {
                border-color: #fff;
                box-shadow: 0 0 25px var(--neon-primary), 0 0 50px var(--neon-secondary);
            }
        }

        .tile {
            width: var(--tile-size);
            height: var(--tile-size);
            background-image: url('/miran.jpg'); 
            background-size: calc(3 * var(--tile-size)) calc(3 * var(--tile-size));
            cursor: pointer;
            transition: transform 0.1s ease-out, opacity 0.1s ease-out;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5rem;
            color: white;
            text-shadow: 1px 1px 2px black;
            user-select: none;
            -webkit-tap-highlight-color: transparent; /* Remove mobile tap highlight */
        }

        .tile:active {
            transform: scale(0.95);
            opacity: 0.8;
        }

        .tile.empty {
            background-image: none !important;
            background-color: #222;
            cursor: default;
        }

        .tile.empty:hover {
            transform: none;
        }

        .congrats {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }

        #countdown-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 8rem;
            font-weight: bold;
        }

        #prize-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 1001;
            padding: 20px;
            overflow-y: auto;
        }

        .prize-content {
            max-width: 600px;
            margin: 40px auto;
            background: #1a1a1a;
            border-radius: 15px;
            border: 2px solid var(--neon-primary);
            box-shadow: 0 0 30px var(--neon-primary);
            padding: 20px;
            text-align: center;
            animation: fadeIn 1s ease-out;
        }

        .prize-img {
            max-width: 100%;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(255,255,255,0.2);
        }

        .prize-info {
            text-align: left;
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-body-tertiary">
    <div id="countdown-overlay"></div>
    
    <div id="prize-modal">
        <div class="prize-content">
            <h2 class="text-primary fw-bold mb-4">You Won a Prize! 🎁</h2>
            <img src="/prize_miran.jpg" alt="Prize" class="prize-img">
            <div class="prize-info border-start border-primary border-4">
                <p class="mb-1"><strong>To.</strong> Miran Jo 🇰🇷</p>
                <p class="mb-1"><strong>Giftee's mobile number:</strong> +82 010-2206-5523</p>
                <p class="mb-0"><strong>Delivery address:</strong> 부산 사하구 윤공단로 90, KR, (49493)</p>
            </div>
            <button onclick="document.getElementById('prize-modal').style.display='none'" class="btn btn-outline-primary mt-4">Close</button>
        </div>
    </div>

    <div class="min-vh-100 d-flex flex-column">
        <header class="container py-4 text-center">
            <h1 class="display-4 fw-bold text-primary mb-2">Miran Puzzle</h1>
            <p class="text-secondary">3x3 Sliding Picture Puzzle</p>
        </header>

        <main class="flex-grow-1 d-flex flex-column align-items-center justify-content-center py-4">
            <div id="congrats-message" class="congrats alert alert-success mb-4 text-center">
                <h4 class="alert-heading">Congratulations!</h4>
                <p class="mb-0">You've solved the puzzle!</p>
            </div>

            <div id="puzzle-board" class="puzzle-board mb-4">
                <!-- Tiles will be generated here -->
            </div>

            <div class="controls d-flex gap-3 mt-2">
                <button id="shuffle-btn" class="btn btn-primary btn-lg px-4">New Game</button>
                <button id="hint-btn" class="btn btn-outline-secondary btn-lg px-4">Numbers On/Off</button>
            </div>
            
            <div class="mt-4 text-center text-secondary small">
                <p>Click on a tile adjacent to the empty square to move it.</p>
            </div>
        </main>

        <footer class="container py-4 text-center text-secondary small">
            &copy; {{ date('Y') }} AdminHR - Miran Page
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const board = document.getElementById('puzzle-board');
            const shuffleBtn = document.getElementById('shuffle-btn');
            const hintBtn = document.getElementById('hint-btn');
            const congratsMsg = document.getElementById('congrats-message');
            
            let tiles = [0, 1, 2, 3, 4, 5, 6, 7, 8]; // 8 is the empty one
            let showNumbers = false;
            let gameStarted = false;

            function renderBoard() {
                board.innerHTML = '';
                tiles.forEach((tileValue, index) => {
                    const tile = document.createElement('div');
                    tile.classList.add('tile');
                    
                    if (tileValue === 8) {
                        tile.classList.add('empty');
                    } else {
                        // Calculate background position
                        const row = Math.floor(tileValue / 3);
                        const col = tileValue % 3;
                        tile.style.backgroundPosition = `calc(${col} * -100%) calc(${row} * -100%)`;
                        
                        if (showNumbers) {
                            tile.innerText = tileValue + 1;
                        }
                    }

                    tile.addEventListener('pointerdown', (e) => {
                        e.preventDefault();
                        handleTileClick(index);
                    });
                    board.appendChild(tile);
                });

                checkWin();
            }

            function handleTileClick(index) {
                const emptyIndex = tiles.indexOf(8);
                if (isAdjacent(index, emptyIndex)) {
                    // Swap
                    [tiles[index], tiles[emptyIndex]] = [tiles[emptyIndex], tiles[index]];
                    renderBoard();
                }
            }

            function isAdjacent(idx1, idx2) {
                const r1 = Math.floor(idx1 / 3);
                const c1 = idx1 % 3;
                const r2 = Math.floor(idx2 / 3);
                const c2 = idx2 % 3;

                const rowDiff = Math.abs(r1 - r2);
                const colDiff = Math.abs(c1 - c2);

                return (rowDiff === 1 && colDiff === 0) || (rowDiff === 0 && colDiff === 1);
            }

            function shuffleTiles() {
                congratsMsg.style.display = 'none';
                board.classList.remove('solved');
                gameStarted = true;
                // To ensure solvability, we perform random valid moves from the solved state
                tiles = [0, 1, 2, 3, 4, 5, 6, 7, 8];
                let emptyIndex = 8;
                
                for (let i = 0; i < 200; i++) {
                    const neighbors = getNeighbors(emptyIndex);
                    const randomNeighbor = neighbors[Math.floor(Math.random() * neighbors.length)];
                    [tiles[emptyIndex], tiles[randomNeighbor]] = [tiles[randomNeighbor], tiles[emptyIndex]];
                    emptyIndex = randomNeighbor;
                }
                renderBoard();
            }

            function getNeighbors(index) {
                const neighbors = [];
                const row = Math.floor(index / 3);
                const col = index % 3;

                if (row > 0) neighbors.push(index - 3);
                if (row < 2) neighbors.push(index + 3);
                if (col > 0) neighbors.push(index - 1);
                if (col < 2) neighbors.push(index + 1);

                return neighbors;
            }

            function checkWin() {
                const isWin = tiles.every((val, idx) => val === idx);
                if (isWin && gameStarted) {
                    congratsMsg.style.display = 'block';
                    board.classList.add('solved');
                    gameStarted = false; 
                    
                    // Initial Confetti
                    confetti({
                        particleCount: 150,
                        spread: 70,
                        origin: { y: 0.6 },
                        colors: ['#00f2ff', '#0062ff', '#ffffff']
                    });

                    // Start Countdown sequence
                    setTimeout(startPrizeSequence, 1500);
                }
            }

            function startPrizeSequence() {
                const overlay = document.getElementById('countdown-overlay');
                overlay.style.display = 'flex';
                let count = 3;
                overlay.innerText = count;

                const timer = setInterval(() => {
                    count--;
                    if (count > 0) {
                        overlay.innerText = count;
                    } else {
                        clearInterval(timer);
                        overlay.style.display = 'none';
                        showPrize();
                    }
                }, 1000);
            }

            function showPrize() {
                const modal = document.getElementById('prize-modal');
                modal.style.display = 'block';
                
                // Fire more confetti!
                const end = Date.now() + (3 * 1000);
                const colors = ['#00f2ff', '#0062ff', '#ffffff', '#ffd700'];

                (function frame() {
                    confetti({
                        particleCount: 5,
                        angle: 60,
                        spread: 55,
                        origin: { x: 0 },
                        colors: colors
                    });
                    confetti({
                        particleCount: 5,
                        angle: 120,
                        spread: 55,
                        origin: { x: 1 },
                        colors: colors
                    });

                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                }());
            }

            shuffleBtn.addEventListener('click', shuffleTiles);
            hintBtn.addEventListener('click', () => {
                showNumbers = !showNumbers;
                renderBoard();
            });

            // Initial render - randomize at start
            shuffleTiles();
        });
    </script>
</body>
</html>
