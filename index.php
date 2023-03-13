<!-- qr_scanner.php -->

<!DOCTYPE html>
<html>
<head>
	<title>QR Scanner</title>
	<style>
		body {
			margin: 0;
			padding: 0;
			background-color: #000;
		}

		video {
			width: 100%;
			height: 100%;
		}

		.container {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
			text-align: center;
			color: #fff;
			font-size: 24px;
		}

		.container p {
			margin: 0;
			padding: 20px;
			background-color: rgba(0, 0, 0, 0.5);
			border-radius: 10px;
			display: inline-block;
		}

		.container p.success {
			background-color: green;
		}

		.container p.error {
			background-color: red;
		}
	</style>
</head>
<body>
	<div class="container">
		<video id="qr-video"></video>
		<p id="qr-status">Scanning QR code...</p>
	</div>

	<script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
	<script src="https://rawgit.com/sitepoint-editors/jsqrcode/master/src/qr_packed.js"></script>
	<script>
		let video = document.getElementById('qr-video');
		let status = document.getElementById('qr-status');

		navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
		.then(function(stream) {
			video.srcObject = stream;
			video.setAttribute('playsinline', true);
			video.play();
			requestAnimationFrame(tick);
		})
		.catch(function(err) {
			status.textContent = err.message;
		});

		function tick() {
			if (video.readyState === video.HAVE_ENOUGH_DATA) {
				qrcode.decode();
			}

			requestAnimationFrame(tick);
		}

		qrcode.callback = function(result) {
			status.textContent = 'Scanning QR code...';
			checkin(result);
		};

		function checkin(qrdata) {
			let url = '<?php echo site_url('absensi/checkin'); ?>';
			let data = {
				qrdata: qrdata
			};

			fetch(url, {
				method: 'POST',
				body: JSON.stringify(data),
				headers: {
					'Content-Type': 'application/json'
				}
			})
			.then(response => {
				if (response.ok) {
					return response.json();
				} else {
					throw new Error('Failed to check in');
				}
			})
			.then(data => {
				let message = data.success ? 'Check-in successful' : 'Check-in failed';
				let className = data.success ? 'success' : 'error';
				status.textContent = message;
				status.classList.remove('success', 'error');
				status.classList.add(className);
			})
			.catch(error => {
				console.error(error);
				status.textContent = 'Failed to check in';
				status.classList.remove('success', 'error');
				status.classList.add('error');
			});
		}
	</script>
</body>
</html>
