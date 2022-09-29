<html>
    <body>
        <h1>Daftar File</h1>
        <?php if ($files): ?>
          <?php echo "<img src='$files' width='150' style='border: 1px solid #ccc' />"; ?>
        <?php endif; ?>
        <pre />
        <?php var_dump($list); ?>


    </body>
</html>
