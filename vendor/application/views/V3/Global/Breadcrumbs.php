          
               <ul class="breadcrumb">
                   <?php
                   $i = 0;
                   $last = count($_breadcrumbs)-1;
                   $uri = "";
                   foreach ($_breadcrumbs as $bc) {
                       $uri.= "/".$bc["url"];
                       $uri = ltrim($uri, "/");
                       // Is it the last element?
                       if($i == $last){
                           print "<li class='active'>";
                           print $bc["name"];
                           print "</li>";
                       } else {
                           print "<li>";
                           print "<a href='".URL::site($uri)."'>".$bc["name"]."</a>";
                           print "<span class='divider'>/</span>";
                           print "</li>\n                   ";
                       }
                       $i++;
                   }
                   ?>
                   
                    <!--<li class="active"><?=$_title?></li>-->
               </ul>
