<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link      http://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Datasource\ConnectionManager;
use Cake\Validation\Validation;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link http://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
    }

    /**
     * Before render callback.
     *
     * @param \Cake\Event\Event $event The beforeRender event.
     * @return void
     */
    public function beforeRender(Event $event)
    {
        $conn = ConnectionManager::get('default');
        
        $actualSeasonId = $this->getActualSeasonId();
        
        $activeClubsInActualSeason = $conn->execute('
            SELECT c.id, c.name
            FROM clubs_seasons cs
            JOIN clubs c ON c.id = cs.club_id
            WHERE cs.season_id = :actual_season_id
        ', ['actual_season_id' => $actualSeasonId], ['actual_season_id' => 'integer']);
        
        $this->set('actualSeasonId', $actualSeasonId);
        $this->set('activeClubsInActualSeason', $activeClubsInActualSeason->fetchAll('assoc'));
        
        if (!array_key_exists('_serialize', $this->viewVars) &&
            in_array($this->response->type(), ['application/json', 'application/xml'])
        ) {
            $this->set('_serialize', true);
        }
    }
    
    protected function getActualSeasonId(){
        $conn = ConnectionManager::get('default');
        
        $actualSeasonId = $conn->query('
            SELECT MAX(id) as actual_season_id 
            FROM seasons
        ')->fetch("assoc");
        
        return (int) $actualSeasonId['actual_season_id'];
    }
    
    protected function associateByColumn(array $array, $column){
        $newArray = array();
        foreach ($array as $element){
            $newArray[$element[$column]] = $element;
        }
        return $newArray;
    }
    
    protected function isNaturalNumber($expectedNaturalNumber){
        return Validation::naturalNumber($expectedNaturalNumber,true);
    }
    
    protected function isAdminLogged(){
        return $this->request->session()->read('admin.login');
    }
    
    public function validateSlovakDateTime($dateTimeStr, $context){
        if(!preg_match('/^[0-3][0-9]\.[0-1][0-9].[0-9]{4} [0-2][0-9]:[0-5][0-9]$/', $dateTimeStr)){
            return false;
        }
        
        $day = substr($dateTimeStr, 0, 2);
        $month = substr($dateTimeStr, 3, 2);
        $year = substr($dateTimeStr, 6, 4);
        
        if(!checkdate($month, $day, $year)){
            return false;
        }
        
        $hour = substr($dateTimeStr, 11, 2);
        $minute = substr($dateTimeStr, 14, 2);
        
        if($hour > 24){
            return false;
        }
        
        return true;
    }
}
