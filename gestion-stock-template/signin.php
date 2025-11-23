<?php
// Preserve legacy route: direct any request to the unified sign-in page
header('Location: signin_client.php');
exit;