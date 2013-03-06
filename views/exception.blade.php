<html>
  <head>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
  </head>
  <body style="padding: 2rem">
    <div class="page-header">
      <h2>Unhandled Exception</h2>
    </div>
    <h3>Message:</h3>
    <p class="lead">{{ $error }}</p>
    <h3>Location:</h3>
    <pre class="well">{{ $file }} on line {{ $line }}</pre>
    <h3>Stack Trace:</h3>
    <pre class="well">{{ $trace }}</pre>
  </body>
</html>