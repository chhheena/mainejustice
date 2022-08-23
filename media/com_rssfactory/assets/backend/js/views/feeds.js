function ShowProgress(){
	el=document.getElementById('div_progress');
	el.style.display='block';
}
function HideProgress(){
	el=document.getElementById('div_progress');
	el.style.display='none';
}
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == "cancel") {
		submitform( pressbutton );
		return;
	}
	if (pressbutton == "publish"){
		document.adminForm.changedPublishedStatus.value=1;
	}
	if (pressbutton == "unpublish"){
		document.adminForm.changedPublishedStatus.value=0;
	}
	submitform( pressbutton );
}

Joomla.submitbutton = function(pressbutton) {
  var form = document.adminForm;

  switch (pressbutton) {
    case 'publish':
      form.changedPublishedStatus.value = 1;
    break;

    case 'unpublish':
      form.changedPublishedStatus.value = 0;
    break;
  }

  Joomla.submitform(pressbutton);
}
