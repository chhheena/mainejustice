<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
$app = JFactory::getApplication();
$input = $app->input;
if (!$input->get('appid')) {
	die('Missing app id!');
}
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="robots" content="noindex">
		<title>Facebook Verify</title>
		<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" />	
		<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css" rel="stylesheet" />	
		<link href="https://cdnjs.cloudflare.com/ajax/libs/spinkit/1.2.5/spinners/4-wandering-cubes.min.css" rel="stylesheet" />	
		<script
			src="https://code.jquery.com/jquery-1.12.4.min.js"
			integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
		crossorigin="anonymous"></script>
	</head>
	<body>
		<div class="login-button" style="text-align:center; margin-top:20px;">
			<div class="fb-login-button" 
				 data-max-rows="1" 
				 data-size="large" 
				 data-button-type="login_with" 
				 data-show-faces="false" 
				 data-auto-logout-link="false" 
				 data-use-continue-as="false"
				 scope="manage_pages"
				 onlogin="checkLoginState"></div>

			<div class="spinner sk-wandering-cubes" style="display:none;">
				<div class="sk-cube sk-cube1"></div>
				<div class="sk-cube sk-cube2"></div>
			</div>
		</div>
		<div class="alert" role="alert" style="text-align:center; display:none;">
		</div>
		<div class="page" style="display:none"></div>
		<div class="pageid" style="display:none;">
		</div>
		<pre class="data-container container" style="display:none"></pre>
		<style>
			.sk-wandering-cubes {
				margin-top: 20px;
			}
			.sk-wandering-cubes .sk-cube {
				background-color: #4166b2;
			}
			.data-container {
				border: solid 1px #ddd;
				padding: 5px;
				border-radius: 5px;
			}
			.page {
				width: 500px;
				margin: 50px auto;
			}
			.pageid {
				text-align: center;
				margin-bottom: 20px;
				font-size: 25px;
			}
		</style>

		<script>
			window.fbAsyncInit = function () {
				FB.init({
					appId: '<?php echo $input->get('appid') ?>',
					cookie: true,
					xfbml: true,
					version: 'v3.3'
				});

				FB.AppEvents.logPageView();

			};

			(function (d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id)) {
					return;
				}
				js = d.createElement(s);
				js.id = id;
				js.src = "https://connect.facebook.net/en_US/sdk.js";
				fjs.parentNode.insertBefore(js, fjs);
			}(document, 'script', 'facebook-jssdk'));

			var checkLoginState = function () {
				$('.fb-login-button').hide();
				$('.spinner').show();
				FB.getLoginStatus(function (response) {
					if (response.status === 'connected') {
						getLongLiveToken(response.authResponse.accessToken);
					} else {
						location.reload();
					}
				});
			}

			var getLongLiveToken = function (access_token) {
				getPages(access_token);
			}

			var verify = function verify (access_token, pageid) {
				$('.login-button').show();

				$.ajax({
					url: '<?php echo JUri::root() ?>',
					method: 'post',
					dataType: 'json',
					data: {
						jado: 'getLongLiveToken',
						access_token: access_token,
						pageid: pageid,
						token: '<?php echo $input->get('token') ?>',
					}
				}).done(function () {
					FB.api(
						'/' + pageid + '/posts?limit=5',
						'GET',
						{"fields": "attachments{type,media_type,media,target,title,url,subattachments,description},permalink_url,story,id,from,message,created_time,updated_time,full_picture,picture"},
						function (json) {
							$('.login-button').hide();
							$('.page select').hide();
							$('.data-container').show().html(JSON.stringify(json.data, null, 2));
							$('.alert').addClass('alert-success bounceIn animated').html('Verify successfully.').show();
							$('.pageid').show().text('Page ID: ' + pageid);
						}
					)
				}).error(function (log) {
					$('.alert').addClass('alert-danger tada animated').html(log.responseText).show();
					$('.login-button').hide();
				});
			}

			var getPages = function getPages (access_token) {
				FB.api(
					'/me/accounts',
					'GET',
					{"fields": "name,id"},
					function (json) {
						var html = '';
						html += '<select class="custom-select">';
						html += '<option value="">Select Page</option>';

						json.data.forEach(function (item) {
							html += '<option value="'+item.id+'">' + item.name + '</option>';
						})

						html += '</select>';

						var $page = $('.page');
						$page.html(html);
						$page.show();

						var $select = $page.find('select');
						$select.on('change', function () {
							$select.attr('disabled', 'disabled');
							var pageid = $select.val();
							verify(access_token, pageid);
						});

						$('.login-button').hide();
					}
				)
			}
		</script>
	</body>
</html>