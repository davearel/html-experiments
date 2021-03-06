<?php ob_start(); ?>
<html>
<head>
	<?php include('../../php/head.php'); writeHead('Particle Text', 'Particle Text', 'http://cacheflowe.com/code/html/experiment/particle-text/preview.gif'); ?>
	<style>
		canvas {
			max-width: 100%;
			margin: 40px auto 20px;
		}
		#word-canvas {
			display: none;
		}
		#instructions.hidden {
			display: none;
		}
		#saved {
			padding: 40px;
		}
		#saved img {
			width: 100%;
			max-width: 700px;
			margin: 0 auto;
		}
	</style>
</head>

<body>
	<header>
		<h1>Particle Text</h1>
	</header>
	<div class="row">
		<div class="twelve columns">
			<canvas id="word-canvas" width="700" height="320"></canvas>
			<canvas id="draw-canvas" width="700" height="320"></canvas>
		</div>
		<div class="twelve columns">
			<input id="text-input" type="text" value="2016"></input>
			<button id="go-button" value="2016">.gif</button>
		</div>
		<div class="twelve columns">
			<div id="instructions" class="hidden">Tap and hold or right click to save the .gif below</div>
			<div id="saved"></div>
		</div>
	</div>
  <script src="../../javascripts/modeset/math_util.js"></script>
  <script src="../../javascripts/modeset/canvas_util.js"></script>
	<script src="../../javascripts/gif/gif.js"></script>
	<script src="../../javascripts/gif/gif-renderer.js"></script>
	<script>
		// draw text to hidden canvas
		var canvasWord = document.getElementById('word-canvas');
		var ctx = canvasWord.getContext('2d');
		ctx.font = "bold 220px Helvetica";
		ctx.fillStyle = "white";
		ctx.textAlign = "center";
		ctx.textBaseline = 'middle';
		var fontSize = 300;
		var numLetters = 0;
		var _imagePixelsData = null;

		// set up text change
		var textInput = document.getElementById('text-input');
		function updateText(e) {
			numLetters = textInput.value.length;
			fontSize = 300 - numLetters * 22;
			ctx.font = "bold " + fontSize + "px Helvetica";
			ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
			ctx.fillStyle = "white";
			ctx.fillText(textInput.value.toUpperCase(), canvasWord.width/2, canvasWord.height * 0.5);
			_imagePixelsData = CanvasUtil.getImageDataForContext( ctx );
			if(goButton != null) goButton.innerHTML = '.gif';
		}
		textInput.addEventListener('input', updateText);
		updateText();

		// set up gif button
		var gifMode = false;
		var gifFrames = 0;
		var gifRenderer = null;
		var instructionsShown = false;
		var goButton = document.getElementById('go-button');
		var savedGifHolder = document.getElementById('saved');
		goButton.addEventListener('click', function(e){
			goButton.innerHTML = 'wait...';
			gifRenderer = new GifRenderer(savedGifHolder);
			clearCanvas();
			for(var i=0; i < particles.length; i++) {
				particles[i].kill();
			}
			gifMode = true;
			gifFrames = 0;
		});

		// setup particle canvas
		var canvasDraw = document.getElementById('draw-canvas');
		var context = canvasDraw.getContext('2d');

		function clearCanvas() {
			context.beginPath();
			context.fillStyle = "rgba(0,0,0,1)";
			context.rect(0, 0, canvasDraw.width, canvasDraw.height);
			context.fill();
		}
		clearCanvas();

		// stupid simple particle object
		function Particle() {
			var _x = 0,
					_y = 0,
					_size = 0,
					_color = '';
			return {
				reset: function(x, y){
					_x = x;
					_y = y;
					_size = 0;
					_color = CanvasUtil.rgbToCanvasColor( MathUtil.randRange(200,255), MathUtil.randRange(100,205), MathUtil.randRange(200,255), 1.0 );
				},
				active: function(){
					return _size < fontSize / 40;
				},
				kill: function(){
					_size = 999;
				},
				update: function(){
					_size += 0.2;
					_y -= 0.2;
					context.fillStyle = _color;
					context.beginPath();
					context.arc(_x, _y, _size, 0, 2*Math.PI);
					context.fill();
				}
			};
		}
		var particles = [];

		function draw() {
			requestAnimationFrame(draw);

			// make 150 particle attempts per frame
			var attempts = 0;
			var launched = 0;
			while(attempts < 400 && launched < numLetters * 5) {
				// pick a random spot on the canvas, looking for white
				var randX = MathUtil.randRange(0,canvasWord.width);
				var randY = MathUtil.randRange(0,canvasWord.height);
				var color = CanvasUtil.getPixelFromImageData(_imagePixelsData, canvasWord.width, randX, randY); //CanvasUtil.getPixelColorFromContext( _contextSource, col, row );
				if(color[0] > 0 && (gifMode == false || gifMode == true && gifFrames < 70)) {
					launched++;
					// find a dead particle, or add a new one
					var foundParticle = false;
					for(var i=0; i < particles.length; i++) {
						if(particles[i].active() == false) {
							particles[i].reset(randX, randY);
							foundParticle = true;
							break;
						}
					}
					if(foundParticle == false) {
						var newParticle = new Particle();
						newParticle.reset(randX, randY);
						particles.push(newParticle);
					}
				}
				attempts++;
			}

			// draw fading overlay
			context.beginPath();
			context.fillStyle = "rgba(0,0,0,0.08)";
			context.rect(0, 0, canvasDraw.width, canvasDraw.height);
			context.fill();

			// update active particles
			for(var i=0; i < particles.length; i++) {
				if(particles[i].active() == true) {
					particles[i].update();
				}
			}

			// gif rendering
			if(gifMode == true) {
				if(gifFrames < 20) {
					var overlay = 1 - (gifFrames/20);
					context.fillStyle = "rgba(0,0,0,"+ overlay +")";
					context.fillRect(0, 0, canvasDraw.width, canvasDraw.height);
				}
				if(gifFrames <= 150) {
					if(gifRenderer != null) gifRenderer.addFrame(context.canvas, 150);
				} else {
					gifMode = false;
				}

				gifFrames++;
			}

			// show instructions
			if(savedGifHolder.childNodes.length > 0) {
				if(instructionsShown == false) {
					instructionsShown = true;
					document.getElementById('instructions').classList.remove('hidden');
				}
			}
		}
		requestAnimationFrame(draw);
	</script>
</body>
</html>
<?php file_put_contents('./index.html', ob_get_contents()); ob_end_flush(); ?>
