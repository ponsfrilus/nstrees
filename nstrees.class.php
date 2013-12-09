<?php

/**
 * **************************
 * * Nested Sets Tree Class *
 * **************************
 *
 * Description:
 * ------------ 
 *  Manage a hierarchical data structure with a flat database table. Using
 *  "Modified Preorder Tree Traversal" method.
 *
 *  Works with PHP versions 4 and 5
 *
 * References:
 * -----------
 *  DB-Model by Joe Celko (http://www.celko.com/)
 *  http://www.sitepoint.com/article/1105/2
 *
 * Datas Structure:
 * ----------------
 *  Handle:
 *    key: 'table'    : Name of the table that contains the tree structure
 *    key: 'lvalname' : Name of the attribute (field) that contains the left value
 *    key: 'rvalname' : Name of the attribute (field) that contains the right value
 *  Node:
 *    key 'l' :  Left value
 *    key 'r' :  Right value
 *
 * Orientation:
 * ------------
 *
 *         n0
 *        / | \
 *      n1  N  n3
 *        /   \
 *      n4     n5
 *
 *   Directions from the perspective of the node N:
 *     n0 : up / ancestor
 *     n1 : previous (sibling)
 *     n3 : next (sibling)
 *     n4 : first (child)
 *     n5 : last (child)
 *
 * @class        nestedTree
 * @author       Rolf Brugger, edutech
 * @contributors Nick Lüthi
 *               Patrick Haederli, HexaDec Sàrl ( http://www.hexadec.ch/ )
 * @version      0.03, 18. April 2005
 * @link         http://www.edutech.ch/contribution/nstrees
 * @license      http://www.gnu.org/licenses/gpl.txt - GNU GPL
 */

class nestedTree
{
  /* Handle, description of the database table and fields */
  var $handle;
  
  /**
  * nestedTree Class Constructor, sets the handle with the
  * database structure values.
  *
  * @params array $nsthandle  Array containing the table name, left
  *                            field name and right field name
  *  @return none
  */
  function nestedTree($nsthandle=array("table"    => "hdl_categories",
                                       "lvalname" => "idleft",
                                        "rvalname" => "idright")) {
    $this->handle = $nsthandle;
  }

  /* *******************************************************************
  *                       Tree Constructors
  * ********************************************************************/
  
  /**
  * Create a new "root" for the tree and return the node
  * 
  * @param array $otherfields  An array of the other field to update with
  *                            the values. ie. : array("fieldname" => "value")
  *    
  * @return mixed  On success, array : the new root Left and Right values 
  *                                    of the node.
  *                On fail, bool : false
  */
  function nstNewRoot($otherfields)
  {
    $newnode['l'] = 1;
    $newnode['r'] = 2;
    if(!$this->_insertNew($newnode, $otherfields)) {
      return false;
    }
    return $newnode;
  }

  /**
  * Create a new child at the first position for the subtree and return the node
  * 
  * @param array $otherfields  An array of the other field to update with
  *                            the values. ie. : array("fieldname" => "value")
  *    
  * @return mixed  On success, array : the Left and Right values 
  *                                    of the new root node.
  *                On fail, bool : false
  */
  function nstNewFirstChild($node, $otherfields)
  {
    $newnode['l'] = $node['l']+1;
    $newnode['r'] = $node['l']+2;
    $this->_shiftRLValues($newnode['l'], 2);
    if($this->_insertNew($newnode, $otherfields)) {
      return $newnode;
    } else {
      return false;
    }
  }

  /**
  * Create a new child at the last position for the subtree and return the node
  * 
  * @param array $otherfields  An array of the other field to update with
  *                            the values. ie. : array("fieldname" => "value")
  *    
  * @return mixed  On success, array : the Left and Right values 
  *                                    of the new node.
  *                On fail, bool : false
  */
  function nstNewLastChild($node, $otherfields)
  /* creates a new last child of 'node'. */
  {
    $newnode['l'] = $node['r'];
    $newnode['r'] = $node['r']+1;
    $this->_shiftRLValues($newnode['l'], 2);
    if($this->_insertNew($newnode, $otherfields)) {
      return $newnode;
    } else {
      return false;
    }
  }
  
  /**
  * Create a new sibling node before the given node (at the same "level")
  * 
  * @param array $node         The sibling Left and Right values of the node
  * @param array $otherfields  An array of the other field to update with
  *                            the values. ie. : array("fieldname" => "value")
  *    
  * @return mixed  On success, array : the Left and Right values 
  *                                    of the new node.
  *                On fail, bool : false
  */
  function nstNewPrevSibling($node, $otherfields)
  {
    $newnode['l'] = $node['l'];
    $newnode['r'] = $node['l']+1;
    $this->_shiftRLValues($newnode['l'], 2);
    if($this->_insertNew($newnode, $otherfields)) {
      return $newnode;
    } else {
      return false;
    }
  }

  /**
  * Create a new sibling node after the given node (at the same "level")
  * 
  * @param array $node         The sibling Left and Right values of the node
  * @param array $otherfields  An array of the other field to update with
  *                            the values. ie. : array("fieldname" => "value")
  *    
  * @return mixed  On success, array : the Left and Right values 
  *                                    of the new node.
  *                On fail, bool : false
  */
  function nstNewNextSibling($node, $otherfields)
  {
    $newnode['l'] = $node['r']+1;
    $newnode['r'] = $node['r']+2;
    $this->_shiftRLValues($newnode['l'], 2);
    if($this->_insertNew($newnode, $otherfields)) {
      return $newnode;
    } else {
      return false;
    }
  }
  
  /**
  * Add a delta to all the Left and Right values that are greater
  * than a first node
  *
  * @param string $first  First value of Right or Left value to increase or decrease
  * @param string $delta  Value of the delta to apply, can be positive or negative
  *
  * @access private
  */
  function _shiftRLValues($first, $delta)
  {
    mysql_query("UPDATE ".$this->handle['table']." SET ".$this->handle['lvalname']
                ."=".$this->handle['lvalname']."+$delta WHERE "
                .$this->handle['lvalname'].">=$first"
                );
    mysql_query("UPDATE ".$this->handle['table']." SET ".$this->handle['rvalname']
                ."=".$this->handle['rvalname']."+$delta WHERE "
                .$this->handle['rvalname'].">=$first"
                );
  }

  /**
  * Add a delta to all the Left and Right values that are greater or equal
  * than a first node and smaller or equal than a last node.
  *
  * @param string $first  First Right or Left value of the range to increase
  *                       or decrease
  * @param string $last   Last Right or Left value of the range to increase
  *                       or decrease
  * @param string $delta  Value of the delta to apply, it can be positive
  *                       or negative
  *
  * @return array  The first and last modified values
  *
  * @access private
  */
  function _shiftRLRange($first, $last, $delta)
  {
    mysql_query("UPDATE ".$this->handle['table']." SET ".$this->handle['lvalname']
                ."=".$this->handle['lvalname']."+$delta WHERE "
                .$this->handle['lvalname'].">=$first AND "
                .$this->handle['lvalname']."<=$last"
                );
    mysql_query("UPDATE ".$this->handle['table']." SET ".$this->handle['rvalname']
                ."=".$this->handle['rvalname']."+$delta WHERE "
                .$this->handle['rvalname'].">=$first AND "
                .$this->handle['rvalname']."<=$last"
                );
    return array('l'=>$first+$delta, 'r'=>$last+$delta);
  }

  /**
  * Insert a new node in the tree (might be a root, child, ...)
  *
  * @param array $node         The Right and Left values of new node
  * @param array $otherfields  An array of the other field to update with
  *                            the values. ie. : array("fieldname" => "value")
  *
  * @return bool  On success, true. On fail, false.
  *
  * @access private
  */
  function _insertNew($node, $otherfields)
  {
    $sqlotherfields = "";
    if(isset($otherfields)) {
      if(is_array($otherfields) && (sizeof($otherfields) > 0)) {
        foreach($otherfields as $fieldname => $fieldvalue) {
          $sqlotherfields .= $fieldname."=".$fieldvalue.",";
        }
      } else {
        return false;
      }
    }
    if(!mysql_query("INSERT INTO ".$this->handle['table']." SET "
                     .$sqlotherfields.$this->handle['lvalname']."="
                     .$node['l'].", ".$this->handle['rvalname']."="
                     .$node['r']
                     )) {
      $this->_prtError();
      return false;
    } else {
      return true;
    }
  }

  /*********************************************************************
  *                        Tree Modification
  **********************************************************************/

  /**
  * Updated specified fields in database for the specified node
  *
  * @param array $node         The Right and Left values of new node to modify
  * @param array $otherfields  An array of the other field to update with
  *                            the values. ie. : array("fieldname" => "value")
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstUpdateNodeFields($node, $otherfields)
  {
    $fields = "";
    foreach($otherfields as $fieldname => $fieldvalue) {
      $fields .= $fieldname."=".$fieldvalue.",";
    }
    $fields = rtrim($fields,",");
    
    return mysql_query("UPDATE ".$this->handle['table']." SET ".$fields
                        ." WHERE ".$this->handle['lvalname']."=".$node['l']
                        );
  }



  /*********************************************************************
  *                       Tree Reorganization
  **********************************************************************/
  
  /**
  * Move a node and all its children to another place as a sibling after
  * the specified sibling.
  *
  * @param array $src  Source node, which will be moved
  * @param array $dst  Destination node that will before the moved node 
  *
  * @return array  New position of the moved subtree.
  */
  function nstMoveToNextSibling($src, $dst)
  {
    return $this->_moveSubtree($src, $dst['r']+1);
  }
  
  /**
  * Move a node and all its children to another place as a sibling before
  * the specified sibling.
  *
  * @param array $src  Source node, which will be moved
  * @param array $dst  Destination node that will be after the moved node 
  *
  * @return array  New position of the moved subtree.
  */
  function nstMoveToPrevSibling($src, $dst)
  {
    return $this->_moveSubtree($src, $dst['l']);
  }
  
  /**
  * Move a node and all its children to another place as the first child
  * of the specified destination node.
  *
  * @param array $src  Source node, which will be moved
  * @param array $dst  Destination node that will be the parent
  *                    of the moved node
  *
  * @return array  New position of the moved subtree.
  */
  function nstMoveToFirstChild($src, $dst)
  {
    return $this->_moveSubtree($src, $dst['l']+1);
  }
  
  /**
  * Move a node and all its children to another place as the last child
  * of the specified destination node.
  *
  * @param array $src  Source node, which will be moved
  * @param array $dst  Destination node that will be the parent
  *                    of the moved node
  *
  * @return array  New position of the moved subtree.
  */
  function nstMoveToLastChild($src, $dst)
  {
    return $this->_moveSubtree($src, $dst['r']);
  }

  /**
  * Move a node and all its children to another place
  *
  * @param array $src  Source node, which will be moved
  * @param array $dst  Destination node
  *
  * @return array  New position of the moved subtree.
  *
  * @access private
  */
  function _moveSubtree($src, $dst)
  { 
    $treesize = $src['r']-$src['l']+1;
    $this->_shiftRLValues($dst, $treesize);
    if($src['l'] >= $dst){ // src was shifted too?
    $src['l'] += $treesize;
      $src['r'] += $treesize;
    }
    /* Now there is enough room next to target to move the subtree */
    $newpos =  $this->_shiftRLRange($src['l'], $src['r'], $dst-$src['l']);
    /* Correct values after source */
    $this->_shiftRLValues($src['r']+1, -$treesize);
    if($src['l'] <= $dst){ // dst was shifted too?
    $newpos['l'] -= $treesize;
      $newpos['r'] -= $treesize;
    }  
    return $newpos;
  }


  /*********************************************************************
  *                          Tree Destructors
  **********************************************************************/

  /**
  * Delete the whole tree structure and content (even the root)
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstDeleteTree()
  {
    if(!mysql_query("DELETE FROM ".$this->handle['table'])) {
      $this->_prtError();
      return false;
    }
    return true;
  }

  /**
  * Delete a node and all its children or keep and outdent all its children
  *
  * @param array $node         The Left and Right values of the node to delete
  * @param bool  $outchildren  true, outdent and keep children
  *                            false, delete children aswell
  *
  * @return mixed  On success, array : The parent or previous sibling node the one
  *                                    that has been deleted
  *                On fail, bool : false
  */
  function nstDelete($node, $outchildren=false)
  {
    $leftanchor = $node['l'];

    if($outchildren) {
      /* Outdent the children if needed and possible */
      if(!$this->nstIsRoot($node)) {
        while($this->nstNbChildren($node) > 0) {
          /* Outdent, we sart from the end to keep the order of children */
          $this->nstMoveToNextSibling($this->nstLastChild($node),$node);
          /* Update the original */
          $node = $this->nstGetNodeWhereLeft($leftanchor);
        }
      } else {
        return false;
      }
    }

    $res = mysql_query("DELETE FROM ".$this->handle['table']." WHERE "
                       .$this->handle['lvalname'].">=".$node['l']." AND "
                       .$this->handle['rvalname']."<=".$node['r']
                       );
    $this->_shiftRLValues($node['r']+1, $node['l'] - $node['r'] -1);

    if(!$res) {
      $this->_prtError();
      return false;
    } else {
      return $this->nstGetNodeWhere($this->handle['lvalname']."<"
                                    .$leftanchor." ORDER BY "
                                     .$this->handle['lvalname']." DESC"
                                    );
    }
  }

  /*********************************************************************
  *                          Tree Queries
  **********************************************************************/

  /**
  * Get the Left and Right values of a node from a SQL query based on any
  * field (You specify the whole WHERE clause).
  *
  * @param string $whereclause  WHERE clause to use to get the values
  *                             of the node. You can specify ORDER BY or
  *                             LIMIT clause here too.
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstGetNodeWhere($whereclause)
  {
    $noderes['l'] = 0;
    $noderes['r'] = 0;
    $res = mysql_query("SELECT * FROM ".$this->handle['table']." WHERE "
                       .$whereclause
                       );
    if(!$res) {
      $this->_prtError();
    }  else {
      if($row = mysql_fetch_array($res)) {
        $noderes['l'] = $row[$this->handle['lvalname']];
        $noderes['r'] = $row[$this->handle['rvalname']];
      }
    }
    return $noderes;
  }

  /**
  * Get Right and Left values of the node that has the specified Left value.
  *
  * @param string $leftval  Left value of the node to get
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstGetNodeWhereLeft($leftval)
  {
    return $this->nstGetNodeWhere($this->handle['lvalname']."=".$leftval);
  }

  /**
  * Get Right and Left values of the node that has the specified Right value.
  *
  * @param string $rightval  Right value of the node to get
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstGetNodeWhereRight($rightval)
  {
    return $this->nstGetNodeWhere($this->handle['rvalname']."=".$rightval);
  }

  /**
  * Get the Left and Right values of the root node.
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstRoot()
  {
    return $this->nstGetNodeWhere($this->handle['lvalname']."=1");
  }

  /**
  * Get Left and Right values of the First child of the specified node.
  *
  * @param array $node  Base node from which the First child will be determined
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstFirstChild($node)
  {
    return $this->nstGetNodeWhere ($this->handle['lvalname']."=".($node['l']+1));
  }

  /**
  * Get Left and Right values of the Last child of the specified node.
  *
  * @param array $node  Base node from which the Last child will be determined
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstLastChild($node)
  {
    return $this->nstGetNodeWhere ($this->handle['rvalname']."=".($node['r']-1));
  }

  /**
  * Get Left and Right values of the Previous sibling of a specified node.
  *
  * @param array $node  Base node from which the Previous sibling will be determined
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstPrevSibling($node)
  {
    return $this->nstGetNodeWhere ($this->handle['rvalname']."=".($node['l']-1));
  }

  /**
  * Get Left and Right values of the Next sibling of a specified node.
  *
  * @param array $node  Base node from which the Next sibling will be determined
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstNextSibling($node)
  {
    return $this->nstGetNodeWhere ($this->handle['lvalname']."=".($node['r']+1));
  }

  /**
  * Get Left and Right values of the ancestor of a node.
  *
  * @param array $node  Base node from which the ancestor will be determined
  *
  * @return array  Left and Right values of the node.
  *                On fail, Left and Right are equal to "0" 
  */
  function nstAncestor($node)
  {
    return $this->nstGetNodeWhere($this->handle['lvalname']."<".($node['l'])
                                  ." AND ".$this->handle['rvalname'].">"
                                  .($node['r'])." ORDER BY "
                                  .$this->handle['rvalname']
                                  );
  }


  /*********************************************************************
  *                         Tree Functions
  **********************************************************************/

  /**
  * Determine if a node is valid or not, just based on Left and 
  * Right values of the specified node (NO SQL query).
  *
  * @param array $node  Node to be validated
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstValidNode($node)
  {
    return ($node['l'] < $node['r']);
  }

  /**
  * Determine if a node has ancestor or not.
  *
  * @param array $node  Node to be analysed
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstHasAncestor($node)
  {
    return $this->nstValidNode($this->nstAncestor($node));
  }

  /**
  * Determine if a node has a previous sibiling node or not.
  *
  * @param array $node  Node to be analysed
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstHasPrevSibling($node)
  {
    return $this->nstValidNode($this->nstPrevSibling($node));
  }

  /**
  * Determine if a node has a next sibiling node or not.
  *
  * @param array $node  Node to be analysed
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstHasNextSibling($node)
  {
    return $this->nstValidNode($this->nstNextSibling($node));
  }

  /**
  * Determine if a node has children or not.
  *
  * @param array $node  Node to be analysed
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstHasChildren($node)
  {
    return (($node['r']-$node['l'])>1);
  }

  /**
  * Determine if a node is the root of the tree.
  *
  * @param array $node  Node to be analysed
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstIsRoot($node)
  {
    return ($node['l']==1);
  }

  /**
  * Determine if a node is a leaf of the tree.
  *
  * @param array $node  Node to be analysed
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstIsLeaf($node)
  {
    return (($node['r']-$node['l'])==1);
  }

  /**
  * Determine if a node is the direct or indirect child of another.
  *
  * @param array $node1  Potential direct or indirect child node
  * @param array $node2  Potential parent node
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstIsChild($node1, $node2)
  {
    return (($node1['l']>$node2['l']) and ($node1['r']<$node2['r']));
  }

  /**
  * Determine if a node is the child of another or if they are equal.
  *
  * @param array $node1  Potential direct or indirect child node
  * @param array $node2  Potential parent node
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstIsChildOrEqual($node1, $node2)
  {
    return (($node1['l']>=$node2['l']) and ($node1['r']<=$node2['r']));
  }

  /**
  * Determine if a node is equal to another.
  *
  * @param array $node1  First node
  * @param array $node2  Second node
  *
  * @return bool  On success, true. On fail, false.
  */
  function nstEqual($node1, $node2)
  {
    return (($node1['l']==$node2['l']) and ($node1['r']==$node2['r']));
  }
  
  /**
  * Get the number of children (direct or not) of a node.
  *
  * @param array $node  Node to analyse
  *
  * @return int  Number of children, 0 if there is no child.
  *              Less than 0 if not isn't a valid node !
  */
  function nstNbChildren($node)
  {
    return (($node['r']-$node['l']-1)/2);
  }

  /**
  * Get the numeric level of a node, root level is 0.
  *
  * @param array $node  Node to analyse
  *
  * @return int  Level of the specified node.
  */  
  function nstLevel($node)
  { 
    $res = mysql_query("SELECT COUNT(*) AS level FROM ".$this->handle['table']
                       ." WHERE ".$this->handle['lvalname']."<".($node['l'])
                       ." AND ".$this->handle['rvalname'].">".($node['r'])
                       );
         
    if($row = mysql_fetch_array ($res)) {
      return $row["level"];
    }else{
      return 0;
    }
  }

  /**
  * Get the popular "Breadcrumbs String", it is the string that describes
  * the full path to a node.
  *   Example : "root > a-node > another-node > current-node"
  *
  * @param array $node  The Left and Right values of the start node.
  *
  * @return array  The path, descending order (root first) to the nood 
  */
  function nstGetBreadcrumbsPath($node, $attribute)
  {
    $breadcrumb = array();
    $breadcrumb[] = $this->nstNodeAttribute($node, $attribute);
    /* Treat ancestor nodes */
    while($this->nstAncestor($node) != array("l"=>0,"r"=>0)){
      $breadcrumb[] = $this->nstNodeAttribute($this->nstAncestor($node),
                                              $attribute
                                              );
      $node = $this->nstAncestor($node);
    }
    /* This is better than using array_unshift to add elements to the array */
    return array_reverse($breadcrumb);
  } 


  /*********************************************************************
  *                         Tree Walks
  **********************************************************************
  *
  * The walks might be tricky to understand at start, but if you 
  * understand the principle of mySQL recordset it's quite obvious.
  *
  * First you have to create the walk handle with nstWalkPreorder and
  * then you can go step by step throught the results with the function
  * nstWalkNext. With the return of nstWalkNext you know if you're at 
  * the end of the structure.
  *
  * And you use your walk handle to get the datas with the function
  * nstWalkAttribute. Every fields of the table are in the query, you
  * just need to know their (field)name !
  *
  **********************************************************************/
  
  /**
  * Create a walk handle to walk through the tree according to
  * the preordered traversal.
  *
  * @param array $node  Start node, only its children will be part
  *                     of the walk
  *
  * @return array  A walk handle to use with other functions
  */
  function nstWalkPreorder($node)
  {
    $res = mysql_query("SELECT * FROM ".$this->handle['table']
           ." WHERE ".$this->handle['lvalname'].">=".$node['l']
           ."   AND ".$this->handle['rvalname']."<=".$node['r']
           ." ORDER BY ".$this->handle['lvalname']);
  
    return array('recset'  =>  $res,
                 'prevl'  =>  $node['l'],  /*  Will be used mark the end        */
                 'prevr'  =>  $node['r'], /*  of the walk with other functions */
                 'level'  =>  -2 );
  }
  
  /**
  * Go to the next step of the walk handle, update the level and get the
  * datas for the current row.
  *
  * @param array &$walkhand  The walk handle (recordset, "start" node, level)
  *
  * @return mixed  Still rows in the recordset, array : The Left and Right values
  *                for the current node (from database).
  *                No more row in the recordset, bool : false
  */
  function nstWalkNext(&$walkhand)
  {
    if($row = mysql_fetch_array ($walkhand['recset'], MYSQL_ASSOC)){
      /* Calculate the level */
      $walkhand['level'] += $walkhand['prevl'] - $row[$this->handle['lvalname']] +2;
      /* Store current node */
      $walkhand['prevl'] = $row[$this->handle['lvalname']];
      $walkhand['prevr'] = $row[$this->handle['rvalname']];
      $walkhand['row']   = $row;
      return array('l'=>$row[$this->handle['lvalname']],
                   'r'=>$row[$this->handle['rvalname']]
                   );
    } else{
      return false;
    }
  }

  /**
  * Get the field value of a specified field from the walk handle recordset
  *
  * @param array $walkhand    The walk handle (recordset, "start" node, level)
  *                           !!! ATTENTION !!! the walk handle recordset must  
  *                           have been used once in nstWalkNext or at least
  *                           been through mysql_fetch_array once.
  * @param string $attribute  The name of the field to get.
  *
  * @return mixed  The value of the field, type depends of the field type.
  */
  function nstWalkAttribute($walkhand, $attribute)
  {
    return $walkhand['row'][$attribute];
  }

  /**
  * Get the Left and Right values of the current node from the walk handle
  *
  * @param array $walkhand  The walk handle (recordset, "start" node, level)
  *
  * @return array  The Left and Right values of the current walk handle.
  */  
  function nstWalkCurrent($walkhand)
  {
    return array('l'=>$walkhand['prevl'], 'r'=>$walkhand['prevr']);
  }

  /**
  * Get the Level od the current node from the walk handle
  *
  * @param array $walkhand  The walk handle (recordset, "start" node, level)
  *
  * @return int  The level of the current node in the walk handle.
  */  
  function nstWalkLevel($walkhand)
  {
    return $walkhand['level'];
  }
  
  
  /*********************************************************************
  *                           Display Tools
  **********************************************************************/
  
  /**
  * Get the field value of the specified node with the field name.
  *
  * @param array $node        The Left and Right values of the node.
  * @param string $attribute  The name of the field to get.
  *
  * @return mixed  If node found in DB and field exsits, string : the value
  *                of the field.
  *                Otherwise, bool : false
  */
  function nstNodeAttribute($node, $attribute)
  {
    $res = mysql_query("SELECT * FROM ".$this->handle['table']." WHERE "
                       .$this->handle['lvalname']."=".$node['l']
                       );
    if($row = mysql_fetch_array($res)) {
      return $row[$attribute];
    } else {
      return false;
    }
  }

  /**
  * Get the field value of the specified node with the field name.
  *
  * @param array $node        The Left and Right values of the node.
  * @param string $attribute  The name of the field to get.
  *
  * @return mixed  If node found in DB and field exsits, string : the value
  *                of the field.
  *                Otherwise, bool : false
  */
  function nstNodeAllAttributes($node)
  {
    $res = mysql_query("SELECT * FROM ".$this->handle['table']." WHERE "
                       .$this->handle['lvalname']."=".$node['l']
                       );
    if($row = mysql_fetch_array($res)) {
      return $row;
    } else {
      return false;
    }
  }

  /**
  * Display a subtree by starting with the specified node.
  *
  * @param array $node         The Left and Right values of the start node.
  * @param string $attributes  An array of the fields values you want to display
  *                            ie. : array("fieldname" => "value").
  *                            Order of the array determins the order of display
  */
  function nstPrintSubtree($node, $attributes)
  {
    $walk = $this->nstWalkPreorder($node);
    while($curr = $this->nstWalkNext($walk)) {
    /* Print Indentation */
    print(str_repeat("&nbsp;", $this->nstWalkLevel($walk)*4));
    /* Print Attributes */
    $att = reset($attributes);
    while($att){
      print ($walk['row'][$att]);
      $att = next($attributes);
    }
    print ("<br/>");
    }
  }
  
  /**
  * Display a subtree by starting with the specified node.
  *
  * @param array $node         The Left and Right values of the start node.
  * @param string $attributes  An array of the fields values you want to display
  *                            ie. : array("fieldname" => "value").
  *                            Order of the array determins the order of display
  *
  * @deprecated Method depracated from 0.02
  */
  function nstPrintSubtree_old($node, $attributes)
  {
    $res = mysql_query("SELECT * FROM ".$this->handle['table']." ORDER BY "
                       .$this->handle['lvalname']
                       );
    if(!$res) {
      $this->_prtError();
    } else {
      $level = -1;
      $prevl = 0;
      while($row = mysql_fetch_array ($res)) {
        /* Calculate level */
        if($row[$this->handle['lvalname']] == ($prevl+1)) {
          $level+=1;
        } elseif($row[$this->handle['lvalname']] != ($prevr+1)) {
          $level-=1;
        }
        /* Add indentation */
        print (str_repeat("&nbsp;", $level*4));
        /* Add attributes */
        $att = reset($attributes);
        while($att){
          print ($att.":".$row[$att]);
          $att = next($attributes);
        }
        print ("<br/>");
        $prevl = $row[$this->handle['lvalname']];
        $prevr = $row[$this->handle['rvalname']];
      }
    }
  }

  /**
  * Display the whole tree (use the subtree display with the root node)
  *
  * @param string $attributes  An array of the fields values you want to display
  *                            ie. : array("fieldname" => "value").
  *                            Order of the array determins the order of display
  */
  function nstPrintTree($attributes)
  { 
    $this->nstPrintSubtree($this->nstRoot(), $attributes);
  }

  
  /*********************************************************************
  *                        Internal Functions
  **********************************************************************/
  
  function _prtError(){
    echo "<p>Error: ".mysql_errno().": ".mysql_error()."</p>";
  }
}
?>