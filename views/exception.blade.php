<html>
  <head>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
  </head>
  <body style="padding: 2rem">
    <h2>Unhandled Exception</h2>
    <h3>Message:</h3>
    <pre>{{ $error }}</pre>
    <h3>Location:</h3>
    <pre>{{ $file }} on line {{ $line }}</pre>
    <h3>Stack Trace:</h3>
    <pre>{{ $trace }}</pre>
  </body>
</html>