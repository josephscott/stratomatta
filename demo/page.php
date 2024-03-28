<?php
declare( strict_types = 1 );
$vars = get_defined_vars();
?>

<pre>
<?php print_r( $vars ); ?>
---
<?php print_r( $GLOBALS ); ?>
</pre>
