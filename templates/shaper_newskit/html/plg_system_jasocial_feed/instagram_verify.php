<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('_JEXEC') or die;
if ($data) {
    $list = array_slice($data, 0, 20);
    
    $items = array_map(function($object) {
        $node = $object->node;
        $item = new stdClass;
        $item->caption = isset($node->edge_media_to_caption->edges[0]->node->text) ? $node->edge_media_to_caption->edges[0]->node->text : '';
        $item->thumb = 'https://instagram.com/p/' . $node->shortcode . '/media/?size=m';
        $item->type = $node->__typename;

        return $item;
    }, $list);
} else {
    $items = array();
}

?>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <title>Instagram Verify</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" />   
        <script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script>
    </head>
    <body>
        <?php if ($items): ?>
        <div class="alert alert-success" role="alert" style="text-align:center;">
            Verify successfully!
        </div>
        
        <div id="container" class="container">
            <h2 style="text-align: center;">Preview</h2>
            <?php foreach ($items as $item): ?>
                <div style="width: 100%; max-width: 30rem; margin-bottom: 5rem; margin-left: auto; margin-right: auto;">
                    <img class="card-img-top" src="<?php echo $item->thumb ?>" alt="Card image cap">
                    <p class="card-text">
                        <span class="badge badge-pill <?php echo $item->type === 'GraphVideo' ? 'badge-danger' : 'badge-primary' ?>">
                            <?php echo $item->type ?>
                        </span>
                         <?php echo $item->caption ?></p>
                </div>
            <?php endforeach ?>
        </div>
        <?php else: ?>
        <div class="alert alert-danger" role="alert" style="text-align:center;">
            Verify error!
        </div>
        <?php endif ?>

        <script>
            imagesLoaded( document.querySelector('#container'), function( instance ) {
                console.log('all images are loaded');
            });
        </script>
    </body>
</html>