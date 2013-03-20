<html>
  <head>
    <link href="http://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
    <style type="text/css">
      body {
        padding: 2rem;
      }
      body, p {
        font-size: 1rem;
      }
      .well {
        padding: 0.75rem;
        box-shadow: none;
        color: #666;
        font-size: 0.75rem;
      }
    </style>
  </head>
  <body>
    <div class="page-header">
      <h3>Unhandled Exception</h3>
      <h5>On the website "{{ $website }}"</h5>
    </div>
    <h4>Message:</h4>
    <p class="lead">{{ $error }}</p>
    <h4>Location:</h4>
    <pre class="well">{{ $file }} on line {{ $line }}</pre>
    <h4>Stack Trace:</h4>
    @foreach($trace as $stack)
      @if (isset($stack['file']))
        <h5>
          <abbr title="{{ $stack['file'] }}">{{ basename($stack['file']) }}</abbr>
          <small>on line {{ $stack['line'] }}</small>
          called <span class="label label-inverse">{{ $stack['function'] }}</span>  with :
        </h5>
        @foreach ($stack['args'] as $arg)
          <pre class="well">{{{ $arg }}}</pre>
        @endforeach
      @endif
    @endforeach
  </body>
</html>