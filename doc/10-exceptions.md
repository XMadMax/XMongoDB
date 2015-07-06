# Exceptions

## Exceptions can be handled by your code:

```php
function MyExceptionHandler($exception) {
    $message = $exception->getMessage();
    $code = $exception->getCode();
    $previous = $exception->getPrevious();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTrace();

    echo "<h2>EXCEPTION</h2>";
    echo "<hr>";
    echo "Message error: $message - $code <br> File: $file  Line: $line <br>";
    echo "Message orig: ".$previous->getMessage()." - ".$previous->getCode()." <br> File: ".$previous->getFile()."  Line: ".$previous->getLine()." <br>";
    echo "<hr>";
    echo "<h2>TRACE</h2>";
    var_dump($trace);
}

set_exception_handler('MyExceptionHandler');
```

