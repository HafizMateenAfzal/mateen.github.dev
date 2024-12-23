
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bounce Bell Game with Restart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f0f0;
        }
        canvas {
            background-color: #181818;
            display: block;
            margin: 0 auto;
            border-radius: 10px;
        }
        #score {
            font-size: 18px;
            color: white;
            text-align: center;
            position: absolute;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
        }
        #restartButton {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            background-color: #6c757d;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        #restartButton:hover {
            background-color: #495057;
        }
        #gameOverMessage {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.7);
            color: #ffcc00;
            font-size: 30px;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.5);
        }
        .bellIcon {
            font-size: 40px;
            color: #FFD700;
        }
        .cozySkateboard {
            background-color: #8b4513;
            border-radius: 12px;
            padding: 10px;
        }
        .redBall {
            background: radial-gradient(circle, rgba(255,0,0,1) 0%, rgba(150,0,0,1) 100%);
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div id="score">Score: 0</div>
    <button id="restartButton" class="btn btn-secondary">Restart Game</button>
    <div id="gameOverMessage">
        <div><i class="fas fa-bell bellIcon"></i></div>
        Game Over! <br> Click Restart to Play Again
    </div>
    <canvas id="gameCanvas"></canvas>

    <script>
        const canvas = document.getElementById("gameCanvas");
        const ctx = canvas.getContext("2d");
        canvas.width = 500;
        canvas.height = 800;

        // Game variables
        let ball, paddle, bell, score, isGameOver;
        const bellSound = new Audio('bell.wav');
        const errorSound = new Audio('error.wav');

        function resetGame() {
            ball = { x: canvas.width / 2, y: canvas.height - 50, radius: 15, dx: 3, dy: -3, color: 'red' };
            paddle = { x: canvas.width / 2 - 60, y: canvas.height - 30, width: 120, height: 20, speed: 7, color: '#8b4513' };
            bell = { x: canvas.width / 2 - 30, y: 50, width: 60, height: 20, color: '#FFD700' };
            score = 0;
            isGameOver = false;
            document.getElementById("gameOverMessage").style.display = "none"; // Hide game over message
            update();
        }

        // Track pressed keys
        let keys = {};

        window.addEventListener("keydown", (e) => {
            keys[e.key] = true;
        });

        window.addEventListener("keyup", (e) => {
            keys[e.key] = false;
        });

        // Mobile controls for paddle
        let touchStartX = 0;

        canvas.addEventListener("touchstart", (e) => {
            touchStartX = e.touches[0].clientX;
        });

        canvas.addEventListener("touchmove", (e) => {
            let touchEndX = e.touches[0].clientX;
            let touchDelta = touchEndX - touchStartX;

            // Adjust paddle position based on touch movement
            paddle.x += touchDelta;
            if (paddle.x < 0) paddle.x = 0;
            if (paddle.x + paddle.width > canvas.width) paddle.x = canvas.width - paddle.width;

            touchStartX = touchEndX; // Update the starting position for the next touch move
        });

        document.getElementById("restartButton").addEventListener("click", resetGame);

        function drawBall() {
            ctx.beginPath();
            ctx.arc(ball.x, ball.y, ball.radius, 0, Math.PI * 2);
            ctx.fillStyle = ball.color;
            ctx.fill();
            ctx.closePath();
        }

        function drawPaddle() {
            ctx.beginPath();
            ctx.rect(paddle.x, paddle.y, paddle.width, paddle.height);
            ctx.fillStyle = paddle.color;
            ctx.fill();
            ctx.closePath();
        }

        function drawBell() {
            ctx.beginPath();
            ctx.rect(bell.x, bell.y, bell.width, bell.height);
            ctx.fillStyle = bell.color;
            ctx.fill();
            ctx.closePath();
        }

        function drawScore() {
            document.getElementById("score").innerText = `Score: ${score}`;
        }

        function update() {
            if (isGameOver) return;

            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // Draw elements
            drawBall();
            drawPaddle();
            drawBell();
            drawScore();

            // Ball movement
            ball.x += ball.dx;
            ball.y += ball.dy;

            // Ball collision with walls
            if (ball.x + ball.radius > canvas.width || ball.x - ball.radius < 0) {
                ball.dx = -ball.dx;
            }
            if (ball.y - ball.radius < 0) {
                ball.dy = -ball.dy;
            } else if (ball.y + ball.radius > canvas.height) {
                // Game over logic
                isGameOver = true;
                errorSound.play();
                document.getElementById("gameOverMessage").style.display = "block"; // Show game over message
                return;
            }

            // Ball collision with paddle
            if (
                ball.x > paddle.x &&
                ball.x < paddle.x + paddle.width &&
                ball.y + ball.radius > paddle.y
            ) {
                ball.dy = -ball.dy;
                let paddleHitPoint = ball.x - (paddle.x + paddle.width / 2);
                ball.dx = paddleHitPoint * 0.1;
            }

            // Ball collision with bell
            if (
                ball.x > bell.x &&
                ball.x < bell.x + bell.width &&
                ball.y - ball.radius < bell.y + bell.height &&
                ball.y + ball.radius > bell.y
            ) {
                score++;
                ball.dy = -ball.dy;
                bellSound.play();
            }

            // Paddle movement (keyboard)
            if (keys["ArrowLeft"] && paddle.x > 0) {
                paddle.x -= paddle.speed;
            }
            if (keys["ArrowRight"] && paddle.x + paddle.width < canvas.width) {
                paddle.x += paddle.speed;
            }

            // Loop update
            requestAnimationFrame(update);
        }

        // Initialize the game
        resetGame();
    </script>
</body>
</html>
