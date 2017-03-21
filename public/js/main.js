(function(document, $){


	$('.issue-form__remove-button').on('click', function(event) {
		event.preventDefault();

		if(!confirm('Внимание! После удаления номер будет недоступен для скачивания купившим его людям. Точно удалить?')) return;

		$.ajax({
			url: '',
			type: 'DELETE',
		})
		.done(function() {
			window.location.replace('/@/');
		})
	});

	/* Issue uploader */
  	var dropzone = $('.new-issue-dropzone');
  	var fileInput = $('.issue-upload-input');

  	if (typeof(window.FileReader) == 'undefined') {
	    dropzone.text('Не поддерживается браузером!');
	}

	dropzone.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
	    e.preventDefault();
	    e.stopPropagation();
	});

	dropzone.on("dragover", function(e){
		dropzone.addClass('issue-upload-drop');
	});
	dropzone.on("dragleave", function(e){
		dropzone.removeClass('issue-upload-drop');
	});
	dropzone.on("drop", function(e){

		dropzone.removeClass('issue-upload-drop');

		var file = e.originalEvent.dataTransfer.files[0];
		upload(file);
	});
	dropzone.on("click", function(e){
		fileInput.click();
	});
	fileInput.on('click', function(e) {
		e.stopPropagation(); // important
	});

	fileInput.on('change', function(e){

		var files = $(this)[0].files;

		upload(files[0]);

	});

	function validateFile(file) {

		var validated = false;
		var _fileformlabel_text = $('.issue-upload-title');

		if(file.type == "application/pdf"){
			dropzone.removeClass('issue-upload-error');

			validated = true;
		} else {
			dropzone.addClass('issue-upload-error');
			_fileformlabel_text.text('Можно загружать только PDF файлы');
		}

		return validated;
	}

	function upload(file){

		if(!validateFile(file)) return;

		formData = new FormData();
		formData.append("issue", file);

		$('.issue-upload-review').hide();


		var preloadInt = 0;
		$.ajax({
			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				xhr.upload.addEventListener("progress", function(evt) {
				  if (evt.lengthComputable) {
					var percentComplete = evt.loaded / evt.total;
					percentComplete = parseInt(percentComplete * 100);
					$('#fileformlabel-text').text(percentComplete+'%');
					$('.issue-upload-form-preloader').show().css('width', ((evt.loaded / evt.total) * 100)+'%');
				  }
				}, false);

				return xhr;
			},
			url: '/@/upload',
			type: 'post',
			contentType: false, // важно - убираем форматирование данных по умолчанию
			processData: false, // важно - убираем преобразование строк по умолчанию
			data: formData,
			dataType: 'json',
			success: function(response){
				$('.issue-upload-form-preloader').hide();
				if (response.success){
					$('.issue-upload-preview').attr('src', '/covers/'+response.hash+'.jpg').show();
					$('input[name="filehash"]').val(response.hash);
				}
			}
		});
  	}

})(document, jQuery);
