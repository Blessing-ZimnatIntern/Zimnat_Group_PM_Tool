<?php

$to = "maisirib@zimnat.co.zw";
$subject = "Overdue Project Alert";

$message = "There are overdue projects that need attention.";

$headers = "From: pmtool@zimnat.co.zw";

mail($to, $subject, $message, $headers);

echo "sent";

?>