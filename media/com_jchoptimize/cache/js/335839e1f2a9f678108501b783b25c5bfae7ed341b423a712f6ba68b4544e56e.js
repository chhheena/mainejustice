
/***!  /media/system/js/keepalive.js?047cd8701353e474d5318e0910fc7bb7  !***/

try {
!function(){"use strict";document.addEventListener("DOMContentLoaded",function(){var o=Joomla.getOptions("system.keepalive"),n=o&&o.uri?o.uri.replace(/&amp;/g,"&"):"",t=o&&o.interval?o.interval:45e3;if(""===n){var e=Joomla.getOptions("system.paths");n=(e?e.root+"/index.php":window.location.pathname)+"?option=com_ajax&format=json"}window.setInterval(function(){Joomla.request({url:n,onSuccess:function(){},onError:function(){}})},t)})}(window,document,Joomla);

} catch (e) {
console.error('Error in file:/media/system/js/keepalive.js?047cd8701353e474d5318e0910fc7bb7; Error:' + e.message);
};
