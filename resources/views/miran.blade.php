<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Miran Puzzle Game</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <style>
        :root {
            --tile-size: 100px;
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
        }

        .tile {
            width: var(--tile-size);
            height: var(--tile-size);
            background-image: url('/miran.jpg'); 
            background-size: calc(3 * var(--tile-size)) calc(3 * var(--tile-size));
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.5rem;
            color: white;
            text-shadow: 1px 1px 2px black;
            user-select: none;
        }

        .tile:hover {
            opacity: 0.9;
            transform: scale(0.98);
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="font-sans antialiased bg-body-tertiary">
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

                    tile.addEventListener('click', () => handleTileClick(index));
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
                    gameStarted = false; // Reset until next shuffle
                }
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
