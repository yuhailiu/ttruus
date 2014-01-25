<?php

// Echo the image - timestamp appended to prevent caching
echo '<a href=""  onclick="refreshimg(); return false;" title="换一张"><img src="/captcha/images/image.php?' . time() . '" id="captcha_image" alt="Captcha image" /></a>';

?>