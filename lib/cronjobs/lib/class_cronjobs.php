<?php
/**
 * Copyright (C) Fabrizio Crisafulli 2012
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace FabCMS;

class cronjobs{
	
	private function checkCronjobs() {
		global $db;
		global $core;
		global $log;
		global $mail;
		global $conf;
		
		// Build the query to check past cronjobs
		$query = 'SELECT * 
				  FROM ' . $db->prefix . 'cronjobs 
				  WHERE enabled = 1
				  AND time <= \'' . date('Y-m-d H:i:s') . '\';';
		
		$db->setQuery($query);
		if (!$result = $db->executeQuery('select')){
			$log->write('cronjob_select_query_error','CRONJOBS','Cannot select cronjobs. ' . $query . ' - ' . $db->lastError );
			return false;
		}
		
		while ($row = mysqli_fetch_array($result)){
			$module = $row['module'];
			$cronjob = $row['cronjob'];
			
			$cronjobPath = $conf['path']['baseDir'] . 'modules/' . $module . '/cronjobs/cronjob_' . $cronjob . '.php'; 
			
			if (!file_exists($cronjobPath)) {
				$log->write('cronjob_load_file_error','CRONJOBS','Cannot open the file. Module ' . htmlentities($module). ', cronjob: ' . htmlentities($cronjob));
			} else {
				if ( (int) $row['omit_log'] !== 1)
					$log->write('cronjob_load_file_info','CRONJOBS','Loading cronjob\'s file. Module ' . htmlentities($module). ', cronjob: ' . htmlentities($cronjob));
				
				include $cronjobPath;
				
			}
		}
	}
}