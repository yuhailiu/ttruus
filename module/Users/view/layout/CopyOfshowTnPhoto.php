<?php

// <!-- The container for the uploaded files-->
echo '<script src="/js/photoDialog.js"></script>';
echo '
<div id="files" class="files" style="height: 149px;">
		<br>
		<a href="#" id="dialog-link">';


echo '          <div id="photoImg1" title="点击放大">
				<img alt="请更新您的头像" id="photoImg" class="ui-state-default ui-corner-all"
				src="';

echo $this->url('users/media', array('action' => 'showImage', 'id' => $user->id, 'subaction' => 'thumb'));
echo '          "
				style="height: 130px; width: 130px" >
                </div>
				</a>
				</div><!-- end files -->';


echo '<!-- ui-dialog -->
<div id="dialog" title="';
echo $user->first_name;
echo '">
<p><img alt="网络繁忙，抱歉。" class="ui-state-default ui-corner-all" src="';
echo $this->url('users/media', array('action' => 'showImage','id' => $user->id));
echo '"></p>
</div>';
