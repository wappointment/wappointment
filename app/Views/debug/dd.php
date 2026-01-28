<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dump and Die</title>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        .dd-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .dd-header {
            font-size: 18px;
            font-weight: bold;
            color: #e3342f;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e3342f;
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            margin: 0;
            font-size: 14px;
            line-height: 1.5;
        }
        .dd-backtrace {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
        }
        .dd-backtrace-header {
            font-size: 14px;
            font-weight: bold;
            color: #666;
            margin-bottom: 10px;
        }
        .dd-backtrace-item {
            font-size: 12px;
            color: #666;
            padding: 4px 0;
        }
        .dump-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            overflow-x: auto;
            border-left: 4px solid #3490dc;
        }
    </style>
</head>
<body>
    <div class="dd-header">Dump and Die</div>
    
    <?php foreach ($vars as $i => $var): ?>
        <div class="dd-container">
            <?php if (count($vars) > 1): ?>
                <div style="color: #666; font-size: 14px; margin-bottom: 10px;">Variable #<?= $i + 1 ?></div>
            <?php endif; ?>
            <pre><?php var_dump($var); ?></pre>
        </div>
    <?php endforeach; ?>
    
    <?php if (!empty($backtrace)): ?>
        <div class="dd-container dd-backtrace">
            <div class="dd-backtrace-header">Call Stack</div>
            <?php foreach ($backtrace as $i => $trace): ?>
                <?php
                    $file = $trace['file'] ?? 'unknown';
                    $line = $trace['line'] ?? 0;
                    $function = $trace['function'] ?? '';
                    $class = $trace['class'] ?? '';
                    
                    if ($class) {
                        $function = $class . $trace['type'] . $function;
                    }
                ?>
                <div class="dd-backtrace-item">
                    <strong>#<?= $i ?></strong> 
                    <?= htmlspecialchars($file) ?>:<?= $line ?>
                    <?php if ($function): ?>
                        <span style="color: #999;">→</span> <?= htmlspecialchars($function) ?>()
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>
