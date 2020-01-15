const $ = require('jquery');
const Routes = require('./Routes');
const moment = require('moment');
const Handlebars = require('handlebars');
require('moment/locale/ru');
moment.locale('ru');
$.fn.updateMoment = function () {
    let $this = $(this);
    $('.moment', $this).each(function () {
        let $this = $(this);
        let m = moment(parseInt($this.data('timestamp')) * 1000);
        let format = $this.data('format');
        if (format === 'from-now') {
            $this.text(m.fromNow());
        } else {
            $this.text(m.format(format));
        }
    });
    return $this;
};
var updateMomentTimer = function () {
    $(document).updateMoment();
    setTimeout(updateMomentTimer, 1e3);
};
updateMomentTimer();

const pjax = require('../Components/Pjax');
pjax.init();
pjax.ready(function () {
    $(document).updateMoment();


    $('input.dropify')
        .removeClass('d-none')
        .dropify({
            messages: {
                'default': 'Перетащите сюда файл или нажмите',
                'replace': 'Перетащите сюда файл для замены',
                'remove': 'Удалить',
                'error': 'Упс, что-то пошло не так.'
            }
        })
        .on('dropify.afterClear change finish', function (ev, response) {
            var $input = $(this);
            let $form = $input.parents('form');
            let xhr = $input.data('xhr');
            if (xhr) {
                xhr.abort();
                $input.removeData('xhr');
            }
            if (ev.type === 'finish' || ev.namespace == 'afterClear') {
                $input
                    .removeAttr('data-uploading')
                    .after(
                        $('<input type="hidden" data-dropify="1">')
                            .attr('name', $input.data('name'))
                            .val(response.id)
                    );
                if (ev.namespace === 'afterClear') {
                    $('div.dropify-progress[data-name="' + $input.data('name') + '"]').remove();
                    $('.dropify-result', $form).remove();
                }
            } else {
                if ($input.val()) {
                    $input.attr('data-uploading', '1');
                } else {
                    $input.removeAttr('data-uploading')
                        .siblings('input[data-dropify][type=hidden][name="' + $input.data('name') + '"]')
                        .remove();
                }
                $('div.dropify-progress[data-name="' + $input.data('name') + '"]').remove();
                $('.dropify-result', $form).remove();

                $input
                    .parents('.dropify-wrapper')
                    .after(
                        $('<div class="progress dropify-progress">').html(
                            '<div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>')
                            .attr('data-name', $input.data('name'))
                    );
            }
            let uploading = $('input.dropify[data-uploading]', $form).length > 0;
            $('button[type="submit"]', $form).toggleClass('d-none', uploading);
            $('.dropify-uploading', $form).toggleClass('d-none', !uploading);
        }).change(function () {
        var $input = $(this);
        var fd = new FormData();
        if (typeof ($input[0].files[0]) == 'undefined') {
            return;
        }
        fd.append('file', $input[0].files[0]);
        $.ajax({
            url: Routes.url('files', 'upload'),
            type: 'post',
            data: fd,
            contentType: false,
            processData: false,
            dataType: 'json',

            xhr: function () {
                var xhr = new window.XMLHttpRequest();
                // Upload progress
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        let percentComplete = Math.floor(evt.loaded / evt.total * 100);
                        let $progress = $('div.dropify-progress[data-name="' + $input.data('name') + '"]');
                        $('div.progress-bar', $progress)
                            .css('width', percentComplete + '%')
                            .attr('aria-valuenow', percentComplete);

                        if (percentComplete == 100) {
                            $progress.delay(500).fadeOut('slow').queue(function (next) {
                                $progress.remove();
                                next();
                            });
                        }
                    }
                }, false);

                $input.data('xhr', xhr);

                return xhr;
            },
            success: function (response) {
                let $form = $input.parents('form');
                var template = Handlebars.compile('<div class="dropify-result mt-5" style="text-align: center;">' +
                    '<a href="{{link}}" class="no-pjax" target="_blank">Скачать</a>' +
                    '<input type="text" class="ml-5" value="{{link}}" size="50" onclick="this.select()" readonly>' +
                    '<div class="mt-3"><a onclick="return confirm(\'Вы уверены?\')" class="no-pjax" href="{{deletion_link}}">Удалить</a></div>' +
                    '</div>');
                let ctx = {
                    link: 'http' + (location.secure ? 's' : '') + '://' + location.hostname + Routes.url('download', {
                        id: response.id,
                        md5: response.md5
                    }),
                    deletion_link: 'http' + (location.secure ? 's' : '') + '://' + location.hostname + Routes.url('delete', {
                        id: response.id,
                        key: response.deletion_key
                    })
                };
                console.log(ctx);
                $form.append(template(ctx));
                $input.trigger('finish', response);
            },
            error: function (response) {
                $input.trigger('finish', response);
            }
        });
    });


});
