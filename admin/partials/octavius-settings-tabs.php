
<h2>Octavius Rocks Settings</h2>
<?php
$tabs = array( 'server' => 'Server', 'ab' => 'A/B Test');

echo '<h2 class="nav-tab-wrapper">';
foreach( $tabs as $tab => $name ){
    $class = ( $tab == $current ) ? ' nav-tab-active' : '';
    echo "<a class='nav-tab$class' href='?page=octavius-rocks&tab=$tab'>$name</a>";

}
echo '</h2>';