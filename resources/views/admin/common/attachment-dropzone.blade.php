<fieldset class="form-horizontal form-border">
    <div class="form-group">
        <div class="col-lg-12">
            <div id="{{$zoneType}}" class="dropzone dz-clickable dz-started" data-type="{{isset($type) ? $type : ''}}"
                 data-tag="{{isset($tag) ? $tag : ''}}">
                <div class="dz-default dz-message">
                    <i class="fa fa-cloud-upload fa-4x"></i> <span>Drop files here to upload</span>
                </div>
                @if (isset($detailAttachments))
                    @foreach($detailAttachments as $detailAttachment)
                        <div class="dz-preview dz-processing dz-image-preview dz-success dz-complete">
                            <div class="dz-image">
                                <img data-dz-thumbnail src="{{$detailAttachment->url ?: url($detailAttachment->relative_path)}}" width="100%">
                            </div>
                            <div class="dz-details">
                                <div class="dz-size"><span data-dz-size=""><strong>0.3</strong> MB</span></div>
                                <div class="dz-filename"><span data-dz-name="">{{$detailAttachment->filename}}</span></div>
                            </div>
                            <a class="dz-remove" href="javascript:undefined;" data-dz-remove>删除图片</a>
                            <input name="attachments[][id]" class="photoName" type="hidden" value="{{$detailAttachment->id}}">
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</fieldset>
<script>
require(['jquery', 'dropzone'], function($, Dropzone) {

    Dropzone.autoDiscover = false;
    $("div#{{$zoneType}}").dropzone({
        url: "/manager/attachments",
        paramName: "file",
        addRemoveLinks: true,
        dictRemoveFile: '删除图片',
        init: function () {
            this.on("sending", function (file, xhr, formData) {
                var $parent = $(file.previewElement).parent();
                var type = $parent.data('type');
                var tag = $parent.data('tag');

                formData.append("type", type);
                formData.append("tag", tag);
            });
            this.on("success", function (file, response) {

                $('<input>').attr({
                    'name': 'attachments[][id]',
                    'type': 'hidden',
                    'value': response.id
                }).appendTo($(file.previewElement));
            });
        }
    });
});
</script>
