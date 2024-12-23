<?php
declare( strict_types = 1 );

// $app is provided when the route handler is a plain PHP file

?>

<h1>Headers</h1>
<pre>
<?php print_r( $app->request->header() ); ?>
</pre>

<hr /> 

<h1>HTTP Version</h1>
<pre>
<?php print_r( $app->request->protocolVersion() ); ?>
</pre>

<hr /> 

<h1>Host</h1>
<pre>
<?php print_r( $app->request->host() ); ?>

<?php print_r( $app->request->host( true ) ); ?>
</pre>

<hr /> 

<h1>Method</h1>
<pre>
<?php print_r( $app->request->method() ); ?>
</pre>

<hr /> 

<h1>URI</h1>
<pre>
<?php print_r( $app->request->uri() ); ?>
</pre>

<hr /> 

<h1>Path</h1>
<pre>
<?php print_r( $app->request->path() ); ?>
</pre>

<hr /> 

<h1>Query String</h1>
<pre>
<?php print_r( $app->request->queryString() ); ?>
</pre>

<hr /> 

<h1>Session</h1>
<pre>
<?php // print_r( $app->request->sessionId() );?>
<?php print_r( $app->session ); ?>
</pre>

<hr /> 

<h1>Cookies</h1>
<pre>
<?php print_r( $app->request->cookie() ); ?>
</pre>

<hr /> 

<h1>GET</h1>
<pre>
<?php print_r( $app->request->get() ); ?>
</pre>

<hr /> 

<h1>POST</h1>
<pre>
<?php print_r( $app->request->post() ); ?>
</pre>

<hr /> 

<h1>Files</h1>
<pre>
<?php print_r( $app->request->file() ); ?>
</pre>

<hr /> 

<h1>App</h1>
<pre>
<?php print_r( $app ); ?>
</pre>

<hr /> 
