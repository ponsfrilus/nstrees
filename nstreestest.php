<?php 
  // Set database connection informations
  $db_host     = "localhost";
  $db_username = "root";
  $db_password = "";
  $db_database = "nstrees";
  
  $handle = array("table"    => "nstrees", // Table name
                  "lvalname" => "lft",        // Left pointer field name
                  "rvalname" => "rgt");       // Right pointer field name
  $namefield = "title";  // Name of the table field with the name of the node

  include "./nstrees.class.php";
  $tree = new nestedTree($handle);
  
  // Connexion à la base de données
  @mysql_connect($db_host, $db_username, $db_password) or die("Impossible de se connecter au serveur de base de données");
  @mysql_select_db($db_database)  or die("Impossible de trouver la base de données")  ?>
<html>
  <head>
    <title>Classe nstrees : Page de test</title>
  </head>
  <body>
    <h1>Classe nstrees : Page de test</h1>
<?php 
  $tree->nstDeleteTree();
  echo ("<h2>Create Root</h2>\n");
  $root = $tree->nstNewRoot(array("name" => "'root'"));
  
  // Print the whole tree
  $tree->nstPrintTree(array($namefield));
  
  
  print ("<h2>Add 4 child nodes</h2>\n");
  $tree->nstNewLastChild($root,array($namefield => "'3'"));
  $tree->nstNewLastChild($tree->nstRoot(),array($namefield => "'4'"));
  // Keep this child in a variable
  $child = $tree->nstNewFirstChild($tree->nstRoot(),array($namefield => "'2'"));
  // Use the previous node to create a node before it in the hierarchy
  $tree->nstNewPrevSibling($child,array($namefield => "'1'"));
  
  // Print the whole tree
  $tree->nstPrintTree(array($namefield));
  
  print ("<h2>Add 3 child subnodes</h2>\n");
  $child = $tree->nstNewFirstChild($tree->nstGetNodeWhere($namefield."='3'"), array($namefield => "'3.3'"));
  $child = $tree->nstNewPrevSibling($child, array($namefield => "'3.1'"));
  $child = $tree->nstNewNextSibling($child, array($namefield => "'3.2'"));
  
  // Print the whole tree  
  $tree->nstPrintTree(array($namefield));
  
  
  print ("<h2>Add 2 child sub-subnodes</h2>\n");
  $child = $tree->nstNewFirstChild($child, array($namefield => "'3.2.1'"));
  $child = $tree->nstNewNextSibling($child, array($namefield => "'3.2.2'"));
  $child = $tree->nstNewFirstChild($child, array($namefield => "'3.2.2.1'"));
  $child = $tree->nstNewFirstChild($child, array($namefield => "'3.2.2.1.1'"));
  $child = $tree->nstNewFirstChild($child, array($namefield => "'3.2.2.1.1.1'"));
  
  // Print the whole tree  
  $tree->nstPrintTree(array($namefield));
  
  print ("<h2>Move subtree 3.2 after 2</h2>\n");
  $child = $tree->nstMoveToNextSibling($tree->nstGetNodeWhere($namefield."='3.2'"), $tree->nstGetNodeWhere($namefield."='2'"));

  // Print the whole tree
  $tree->nstPrintTree(array($namefield));
  
  
  print ("<h2>Move subtree 3.2 back previous to 3.3</h2>\n");
  $child = $tree->nstMoveToPrevSibling($tree->nstGetNodeWhere($namefield."='3.2'"), $tree->nstGetNodeWhere($namefield."='3.3'"));

  // Print the whole tree  
  $tree->nstPrintTree(array($namefield));
  
  print ("<h2>Print node properties</h2>\n");
  $node = $tree->nstGetNodeWhere($namefield."='3.2'");
  print ("First child of 3.2:&nbsp; ".$tree->nstNodeAttribute($tree->nstFirstChild($node), $namefield)."<br />\n");
  print ("Last child of 3.2:&nbsp; ".$tree->nstNodeAttribute($tree->nstLastChild($node), $namefield)."<br />\n");
  print ("Prev sibling of 3.2:&nbsp; ".$tree->nstNodeAttribute($tree->nstPrevSibling($node), $namefield)."<br />\n");
  print ("Next sibling of 3.2:&nbsp; ".$tree->nstNodeAttribute($tree->nstNextSibling($node), $namefield)."<br />\n");
  print ("Ancestor of 3.2:&nbsp; ".$tree->nstNodeAttribute($tree->nstAncestor($node), $namefield)."<br />\n");
  
  $node = $tree->nstGetNodeWhere($namefield."='3'");
  print ("<br />First child of 3:&nbsp; ".$tree->nstNodeAttribute($tree->nstFirstChild($node), $namefield)."<br />\n");
  print ("Last child of 3:&nbsp; ".$tree->nstNodeAttribute($tree->nstLastChild($node), $namefield)."<br />\n");
  print ("Prev sibling of 3:&nbsp; ".$tree->nstNodeAttribute($tree->nstPrevSibling($node), $namefield)."<br />\n");
  print ("Next sibling of 3:&nbsp; ".$tree->nstNodeAttribute($tree->nstNextSibling($node), $namefield)."<br />\n");
  print ("Ancestor of 3:&nbsp; ".$tree->nstNodeAttribute($tree->nstAncestor($node), $namefield)."<br />\n");
  
  $node = $tree->nstGetNodeWhere($namefield."='1'");
  print ("<br />First child of 1:&nbsp; ".$tree->nstNodeAttribute($tree->nstFirstChild($node), $namefield)."<br />\n");
  print ("Last child of 1:&nbsp; ".$tree->nstNodeAttribute($tree->nstLastChild($node), $namefield)."<br />\n");
  print ("Prev sibling of 1:&nbsp; ".$tree->nstNodeAttribute($tree->nstPrevSibling($node), $namefield)."<br />\n");
  print ("Next sibling of 1:&nbsp; ".$tree->nstNodeAttribute($tree->nstNextSibling($node), $namefield)."<br />\n");
  print ("Ancestor of 1:&nbsp; ".$tree->nstNodeAttribute($tree->nstAncestor($node), $namefield)."<br />\n");
  
  print ("<h2>Print Boolean properties (1=yes, empty=no)</h2>\n");
  $node = $tree->nstGetNodeWhere($namefield."='3.2'");
  print ("Node 3.2:&nbsp;&nbsp; has anc:".$tree->nstHasAncestor($node)
        ." &nbsp;has next sibl:".$tree->nstHasNextSibling($node)
        ." &nbsp;has prev sibl:".$tree->nstHasPrevSibling($node)
        ." &nbsp;has children:".$tree->nstHasChildren($node)
        ."<br />\n");
  $node = $tree->nstGetNodeWhere($namefield."='3'");
  print ("Node 3:&nbsp;&nbsp; has anc:".$tree->nstHasAncestor($node)
        ." &nbsp;has next sibl:".$tree->nstHasNextSibling($node)
        ." &nbsp;has prev sibl:".$tree->nstHasPrevSibling($node)
        ." &nbsp;has children:".$tree->nstHasChildren($node)
        ."<br />\n");
  $node = $tree->nstGetNodeWhere($namefield."='1'");
  print ("Node 1:&nbsp;&nbsp; has anc:".$tree->nstHasAncestor($node)
        ." &nbsp;has next sibl:".$tree->nstHasNextSibling($node)
        ." &nbsp;has prev sibl:".$tree->nstHasPrevSibling($node)
        ." &nbsp;has children:".$tree->nstHasChildren($node)
        ."<br />\n");
  $node = $tree->nstRoot();
  print ("Node root:&nbsp;&nbsp; has anc:".$tree->nstHasAncestor($node)
        ." &nbsp;has next sibl:".$tree->nstHasNextSibling($node)
        ." &nbsp;has prev sibl:".$tree->nstHasPrevSibling($node)
        ." &nbsp;has children:".$tree->nstHasChildren($node)
        ."<br />\n");
  
  print ("<h2>Print level values</h2>\n");
  print ("Level of root= ".$tree->nstLevel($tree->nstRoot())."<br />\n");
  print ("Level of node 3= ".$tree->nstLevel($tree->nstGetNodeWhere($namefield."='3'"))."<br />\n");
  print ("Level of node 3.2= ".$tree->nstLevel($tree->nstGetNodeWhere($namefield."='3.2'"))."<br />\n");
  print ("Level of node 3.2.1= ".$tree->nstLevel($tree->nstGetNodeWhere($namefield."='3.2.1'"))."<br />\n");
      
  print ("<h2>Walk tree - Preorder:</h2>\n");
  $walk = $tree->nstWalkPreorder ($tree->nstRoot());
  print ("INIT-LEVEL=".$tree->nstWalkLevel($walk)."<br />\n");
  while($node = $tree->nstWalkNext($walk)){
    print ("L".$tree->nstWalkLevel($walk).":next"
          .$tree->nstNodeAttribute($node, $namefield)."-curr"
          .$tree->nstNodeAttribute($tree->nstWalkCurrent($walk), $namefield)." "
          ." &nbsp;<br /> ");
  }
  print ("<br />\n");

  print ("<h2>Delete subtree 3.2 without keeping children</h2>\n");
  $child = $tree->nstDelete($tree->nstGetNodeWhere($namefield."='3.2'"),false);
  
  // Print the whole tree  
  $tree->nstPrintTree(array($namefield));

  print ("<h2>Delete subtree 3 and outdent children</h2>\n");
  $child = $tree->nstDelete($tree->nstGetNodeWhere($namefield."='3'"),true);
  
  // Print the whole tree  
  $tree->nstPrintTree(array($namefield));

  mysql_close();
?>
  </body>
</html>