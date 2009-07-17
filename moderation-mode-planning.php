<?php
/**
 * @package Moderation mode planning
 * @author Thibault RADELET
 * @version 1.0
 */
/*
Plugin Name: Moderation mode planning
Plugin URI: http://thibaultradelet.com/dev/
Description: This pluging allow you to program your moderation mode, it automatically updates the comment_moderation option, as the days and time slot selected
Author: Thibault Radelet
Version: 1.0
Author URI: http://thibaultradelet.com/dev/
*/


/* On ajoute la page d'option dans le back-end */
add_action('admin_menu', 'mmp_add_admin_pages');
function mmp_add_admin_pages() {
	add_options_page('Planning de modération', 'Planning de modération', 10, __FILE__, 'mmp_options_page');
}

/* Si le plugin est supprimmé, on supprimme les options correspondantes dans la table Options */
add_action('deactivate_moderation-mode-planning/moderation-mode-planning.php', 'mmp_deactivate');
function mmp_deactivate() {
	delete_option("mmp_lundi");
delete_option("mmp_mardi");
delete_option("mmp_mercredi");
delete_option("mmp_jeudi");
delete_option("mmp_vendredi");
delete_option("mmp_samedi");
delete_option("mmp_dimanche");

delete_option("mmp_heure_start");
delete_option("mmp_heure_stop");
delete_option("mmp_min_start");
delete_option("mmp_min_stop");
}

/* Le traitement des critères séléctionnés par le formulaire se fait ici, 
On créé et met à jour les options en correspondance dans la table options */
add_action('init', 'mmp_setoptions');
function mmp_setoptions() {
	//si on est dans le cas de la validation du formulaire uniquement
	if(!empty($_POST['post'])) {
		if(!empty($_POST['mmp_lundi']))
			update_option("mmp_lundi",$_POST['mmp_lundi']);
		else 
			update_option("mmp_lundi",0);
		if(!empty($_POST['mmp_mardi'])) {
			update_option("mmp_mardi",$_POST['mmp_mardi']);
		}
		else 
			update_option("mmp_mardi",0);
		if(!empty($_POST['mmp_mercredi'])) {
			update_option("mmp_mercredi",$_POST['mmp_mercredi']);
		}
		else 
			update_option("mmp_mercredi",0);
		if(!empty($_POST['mmp_jeudi'])) {
			update_option("mmp_jeudi",$_POST['mmp_jeudi']);
		}
		else 
			update_option("mmp_jeudi",0);
		if(!empty($_POST['mmp_vendredi'])) {
			update_option("mmp_vendredi",$_POST['mmp_vendredi']);
		}
		else 
			update_option("mmp_vendredi",0);
		if(!empty($_POST['mmp_samedi'])) {
			update_option("mmp_samedi",$_POST['mmp_samedi']);
		}
		else 
			update_option("mmp_samedi",0);
		if(!empty($_POST['mmp_dimanche'])) {
			update_option("mmp_dimanche",$_POST['mmp_dimanche']);
		}
		else 
			update_option("mmp_dimanche",0);
	
		if(!empty($_POST['mmp_heure_start'])) {
			update_option("mmp_heure_start",$_POST['mmp_heure_start']);
		}
		if(!empty($_POST['mmp_heure_stop'])) {
			update_option("mmp_heure_stop",$_POST['mmp_heure_stop']);
		}
		if(!empty($_POST['mmp_min_start'])) {
			update_option("mmp_min_start",$_POST['mmp_min_start']);
		}
		if(!empty($_POST['mmp_min_stop'])) {
			update_option("mmp_min_stop",$_POST['mmp_min_stop']);
		}
	}
}


/* Mije à jour de la variable comment_moderation en fonction des critères sélectionnés, 
cette fonction sera appelé à chaque mise à jour d'une page du blog */

add_action('init', 'change_moderation_mode', 10);
function change_moderation_mode() {
	/* on test par rapport à la date d'aujourd'hui */
	switch (date('N')) {
	case 1:
    if (get_option("mmp_lundi")==1) // si c'est un jour ou la modération est obligatoire, on actualise la variable
		update_option("comment_moderation", get_option("mmp_lundi"));
	else // si on n'est pas dans un jour de modération obligatoire, on vérifie la plage horaire.
		check_hours();
    break;
	case 2:
    if (get_option("mmp_mardi")==1) 
		update_option("comment_moderation", get_option("mmp_mardi"));
		else 
		check_hours();
    break;
	case 3:
    if (get_option("mmp_mercredi")==1) 
		update_option("comment_moderation", get_option("mmp_mercredi"));
		else 
		check_hours();
    break;
	case 4:
    if (get_option("mmp_jeudi")==1) 
		update_option("comment_moderation", get_option("mmp_jeudi"));
		else 
		check_hours();
    break;
	case 5:
    if (get_option("mmp_vendredi")==1) 
		update_option("comment_moderation", get_option("mmp_vendredi"));
		else 
		check_hours();
    break;
	case 6:
    if (get_option("mmp_samedi")==1) 
		update_option("comment_moderation", get_option("mmp_samedi"));
		else 
		check_hours();
    break;
	case 7:
    if (get_option("mmp_dimanche")==1) 
		update_option("comment_moderation", get_option("mmp_dimanche"));
		else 
		check_hours();
    break;
	}
}

/* fonction de vérification de la plage horaire */
function check_hours() {
	
	/* on calcule l'heure du jour en minute*/
	$current_hours = date(G);
	$current_min = date(i);
	$total_min=$current_hours*60+$current_min;

	/* on calcule l'heure de début en minute */
	$start_hours = get_option("mmp_heure_start");
	$start_min = get_option("mmp_min_start");
	$total_start_min = $start_hours*60+$start_min;
	
	/* on calcule l'heure de fin en minute */
	$stop_hours = get_option("mmp_heure_stop");
	$stop_min = get_option("mmp_min_stop");
	$total_stop_min = $stop_hours*60+$stop_min;
	
	/* on calule la durée de la plage horraire */
	$delta = $total_stop_min - $total_start_min;
	
	
	//si l'heure de début > heure de fin, la page dure plus de 12h
	if( $delta<=0 ) {
		if( $total_min >= $total_stop_min && $total_min <= $total_start_min) { //en dehort de la plage, plage inversé car +12h = on peut commenter librement
			update_option("comment_moderation", 0);
		}
		else { // si on est dans la plage, on ne peut pas
			update_option("comment_moderation", 1);
		}
	}
	else {
		if( $total_min >= $total_start_min && $total_min <= $total_stop_min) { // si on est dans la plage, on ne peut pas commenter librement
			update_option("comment_moderation", 1);
		}
		else {
			update_option("comment_moderation", 0);
		}
	}
}

/* La fonction d'affichage de l'interface */
function mmp_options_page() {

?>
	<div id="poststuff" class="metabox-holder">

	
		<h2>Planification du mode de modération</h2>
		
			<?php if(!empty($_POST['post'])) { 
	echo'<div class="updated"><p><strong>Paramètres enregistrés.</strong></p></div><br/>';
	} ?>
		
		<div class="postbox">
			<h3 class="hndle"><span>Infos</span></h3>
			<div class="inside">
				<p>Ce plugin permet de planifier le mode de modération selon les options que vous allez définir ici.<br/>
				<b><i>ATTENTION,</i></b> Il réécrit automatiquement la variable wordpress "Un administrateur doit toujours approuver le commentaire" !</p>
			</div>
		</div>
		
		<div class="postbox">
			<h3 class="hndle"><span>Options de configuration</span></h3>
			<div class="inside">
		<b>Selectionnez les jours de la semaine ou la modération est obligatoire (validation par un modérateur) :</b><br/><br/>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<table style="padding: 10px; border: 1px solid #cccccc;">
			<tr><td width="100">
				<INPUT type='checkbox' name="mmp_lundi" <?php if (get_option("mmp_lundi") == 1) { ?> value="1" checked <?php } else { ?> value="1" <?php } ?> />Lundi
				</td>
				<td width="100">
				<INPUT type='checkbox' name="mmp_mardi" <?php if (get_option("mmp_mardi") == 1) { ?> value="1" checked <?php } else { ?> value="1" <?php } ?> />Mardi
				</td>
				<td width="100">
				<INPUT type='checkbox' name="mmp_mercredi" <?php if (get_option("mmp_mercredi") == 1) { ?> value="1" checked <?php } else { ?> value="1" <?php } ?> />Mercredi
				</td>
				<td width="100">
				<INPUT type='checkbox' name="mmp_jeudi" <?php if (get_option("mmp_jeudi") == 1) { ?> value="1" checked <?php } else { ?> value="1" <?php } ?> />Jeudi
				</td>
				<td width="100">
				<INPUT type='checkbox' name="mmp_vendredi" <?php if (get_option("mmp_vendredi") == 1) { ?> value="1" checked <?php } else { ?> value="1" <?php } ?> />Vendredi<br />
				</td>
			</tr>
			<tr><td>
				<INPUT type='checkbox' name="mmp_samedi" <?php if (get_option("mmp_samedi") == 1) { ?> value="1" checked <?php } else { ?> value="1" <?php } ?> />Samedi
				</td>
				<td>
				<INPUT type='checkbox' name="mmp_dimanche" <?php if (get_option("mmp_dimanche") == 1) { ?> value="1" checked <?php } else { ?> value="1" <?php } ?> />Dimanche
				</td>
				<td></td>
				<td></td>
				<td></td>
			<tr>
		</table>
		<br/><br/>
		<b>Selectionnez la plage horraire ou la modération est obligatoire (validation par un modérateur) :</b><br/><br/>
		<table style="padding: 10px; border: 1px solid #cccccc;">
			<tr>
				<td width="100">	
				Heure de début :
				</td>
				<td width="100">
				<INPUT type="text" value="<?php echo get_option("mmp_heure_start"); ?>" name="mmp_heure_start" size="2" maxlength="2">:<INPUT type="text" value="<?php echo get_option("mmp_min_start"); ?>" name="mmp_min_start" size="2" maxlength="2">
				</td>
				<td width="100">
				</td>
				<td width="100">	
				Heure de fin :
				</td>
				<td width="100">
				<INPUT type="text" value="<?php echo get_option("mmp_heure_stop"); ?>" name="mmp_heure_stop" size="2" maxlength="2">:<INPUT type="text" value="<?php echo get_option("mmp_min_stop"); ?>" name="mmp_min_stop" size="2" maxlength="2">
				</td>
			</tr>
		</table>
		<legend>Ce paramètre s'appliquera uniquement sur les jours qui ne sont pas sélectionnés</legend>
		</div>
		</div>
		
		<INPUT type='hidden' name="post" VALUE= "1">
		<div class="submit">
			<input type="submit" name="mmp_submit" value="Enregistrer les changements" style="font-weight:bold;" />
		</div>	   
		</form>
	</div>
<?php
}
?>