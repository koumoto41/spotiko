<!doctype html>
<html lang="jp">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>MY TOP</title>
    </head>
    <body>
        <div>
            <table>
                <?php foreach ($artist as $key => $value): ?>
                <tr>
                    <td><?= $key ?></td>
                    <td><?= $value->name ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </body>
</html>
