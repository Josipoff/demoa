<?php if(count($this->customSlideshowImagesData)) { ?>
<table id="custom-slideshow-images" cellspacing="0" cellpadding="0" class="mt-table">
    <thead>
        <tr>
            <th title="">#</th>
            <th title="">Delete</th>
            <th title="">Order</th>
            <th title="">Exclude</th>
            <?php if(!empty($this->languagesData)) { ?>
            <th title="">Lang</th>
            <?php } ?>
            <th title="">Image</th>
            <th title="">Title/Description/Link</th>
        </tr>
    </thead>
    <?php foreach($this->customSlideshowImagesData as $index => $imageData) { ?>
    <tr id="row-<?php echo $imageData['id']; ?>">
        <td><?php echo $index+1; ?></td>
        <td>
            <a href="#" onclick="return deleteImage('<?php echo $imageData['id']; ?>');" title="Delete image"><span class="mt-icon-trash"></span></a>
            <input type="hidden" name="images-update-data[<?php echo $imageData['id']; ?>][delete]" id="delete-<?php echo $imageData['id']; ?>" value="0"/>
        </td>
        <td>
            <input type="text" name="images-update-data[<?php echo $imageData['id']; ?>][order]" value="<?php echo $imageData['order']; ?>" class="mt-input-order"/>
        </td>
        <td>
            <input type="checkbox" name="images-update-data[<?php echo $imageData['id']; ?>][exclude]" value="<?php echo $imageData['exclude']; ?>"<?php if($imageData['exclude']) { ?> checked="checked"<?php } ?>/>
        </td>
        <?php if(!empty($this->languagesData)) { ?>
        <td>
            <select name="images-update-data[<?php echo $imageData['id']; ?>][lang]">
                <option value="0" <?php if(!$imageData['lang']) { ?>selected="selected"<?php } ?>>all</option>
                <?php foreach($this->languagesData as $language) { ?>
                <option value="<?php echo $language['id']; ?>"<?php
                    if($imageData['lang'] == $language['id']) { echo ' selected="selected"'; }
                    if(!$language['active']) { echo ' disabled="disabled"'; }
                ?>><?php echo $language['code']; ?></option>
                <?php } ?>
            </select>
        </td>
        <?php } ?>
        <td>
            <img src="<?php echo $this->imageBaseUrl.$imageData['name']; ?>" alt="<?php echo basename($imageData['name']); ?>" title="<?php echo basename($imageData['name']); ?>" style="width: 60px; height: 60px;" />
            <input type="hidden" name="images-update-data[<?php echo $imageData['id']; ?>][name]" value="<?php echo $imageData['name']; ?>" />
        </td>
        <td class="mt-slide-td">
            <b>Title:</b>
            <input type="text" name="images-update-data[<?php echo $imageData['id']; ?>][title]" value="<?php echo $imageData['title']; ?>">
            <b>Description:</b>
            <textarea name="images-update-data[<?php echo $imageData['id']; ?>][description]"><?php echo $imageData['description']; ?></textarea>
            <b>Link:</b>
            <input type="text" name="images-update-data[<?php echo $imageData['id']; ?>][link]" value="<?php echo $imageData['link']; ?>">
        </td>
    </tr>
    <?php } ?>
</table>
<?php } ?>
<div class="mt-upload-container">
    <input type="button" class="mt-upload-button mt-border-r-4px" value="Upload images"/>
    <input class="mt-upload-file" type="file" name="magicscroll-image-files[]" id="upload-file" multiple="multiple" accept="image/*" size="1" onchange="uploadFiles();"/>
</div>

<script type="text/javascript">
//<![CDATA[

function uploadFiles() {
    $('#magicscroll-submit-action').val('upload');
    $('#magictoolbox-settings-form').submit();
}

function deleteImage(imageId) {
    if(parseInt($('#delete-'+imageId).val())) {
        $('#row-'+imageId).removeClass('mt-delete');
        $('#delete-'+imageId).val(0);
    } else {
        $('#row-'+imageId).addClass('mt-delete');
        $('#delete-'+imageId).val(1);
    }
    return false;
}

//]]>
</script>
