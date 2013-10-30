<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<?php
	require_once '../../../../../config_path.inc.php';
?>
<html>
    <head>
        <title>Audio file embedder</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta content="noindex, nofollow" name="robots">
		<script type="text/javascript" src="../../../../../include/PHPjavascript.php"></script>
		<script type="text/javascript" src="media.js"></script>
    </head>
    <body onload="OnLoad();">
        <table id="otable" cellspacing="0" cellpadding="3" width="100%" border="0">
			<tr>
				<td>
					<label for="choose"><span fckLang="DlgMediaChoose">DlgMediaChoose</span>:</label>
					<select id="type" onchange="toggleSize(this);">
						<option value=""  fckLang="DlgMediaEmpty">DlgMediaEmpty</option>
					</select>
					<script type="text/javascript">
						var select = document.getElementById("type");
						var options = new Array();
						options.push(document.createElement('option'));
						options[0].setAttribute('value', MEDIA_SOUND);
						options[0].setAttribute('fckLang', 'DlgMediaAudio');
						options[0].text = 'DlgMediaAudio';

						options.push(document.createElement('option'));
						options[1].setAttribute('value', MEDIA_VIDEO);
						options[1].setAttribute('fckLang', 'DlgMediaVideo');
						options[1].text = 'DlgMediaVideo';

						for(var i = 0; i < options.length; i++) {
							try {
							  select.add(options[i],select.options[null]);
							}
							catch (e) {
							  select.add(options[i],null);
							}
						}
					</script>
				</td>
			</tr>
            <tr>
                <td>
                    <label for="media"><span fckLang="DlgMediaLabel">DlgMediaLabel</span>:</label>
					<br />
                    <input id="value" type="text" readonly />
					<input type="button" onclick="fileBrowser();" fckLang="DlgMediaBrowse" value="DlgMediaBrowse" />
                </td>
            </tr>
			<tr>
				<td>
					<span fckLang="DlgMediaTitle">DlgMediaTitle</span>: <input id="rel" type="text" value="" />
				</td>
			</tr>
            <tr id="only_video" style="display:none;">
				<td>
					<b><span fckLang="DlgMediaSize">DlgMediaSize</span></b>
					<br />
					<span fckLang="DlgMediaWidth">DlgMediaWidth</span>: <input id="width" type="text" size="4" value="<?php echo DEFAULT_VIDEO_WIDTH;  ?>" />
					<br />
					<span fckLang="DlgMediaHeight">DlgMediaHeight</span>: <input id="height" type="text" size="4" value="<?php echo DEFAULT_VIDEO_HEIGHT;  ?>" />
				</td>
			</tr>
        </table>
    </body>
</html>

