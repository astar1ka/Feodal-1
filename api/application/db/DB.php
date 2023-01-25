<?php
class DB {
    function __construct($config,$cache) {
        $host = $config["host"];
        $port = $config["port"];
        $name = $config["name"];
        $user = $config["user"];
        $password = $config["password"];
        $this->cache = $cache;

        try {
            $this->db = new PDO(
                'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $name,
                $user,
                $password
            );
        } catch (Exception $e) {
            print_r($e->getMessage());
            die;
        }
    }

    function __destruct() {
        $this->db = null;
    }

    private function getArray($query) {
        $stmt = $this->db->query($query);
        if ($stmt) {
            $result = array();
            while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
                $result[] = $row;
            }
            return $result;
        }
    }

    // only for string
    private function simpleUpdate($table, $field, $value) {
        $query = 'UPDATE '.$table.' SET '.$field.'="'.$value.'"';
        $this->db->query($query);
        return true;
    }

    private function selectWithCondition($table, $fields, $condition ,$value) {
        $query = 'SELECT ' . $fields .' FROM ' . $table . ' WHERE ' . $condition . '=?';
        $sth = $this->db->prepare($query);
        $this->db->quote($value);
        $sth->execute([$value]);
        return $sth;
    }

    ////////////////////////////////////////
    //////////////forUser///////////////////
    ////////////////////////////////////////

    public function getUser($login) {
        $query = '
            SELECT * 
            FROM users 
            WHERE login="' . $login . '"';
        return $this->db->query($query)->fetchObject();
    }

    public function getUserByToken($token) {
        $query = '
            SELECT id 
            FROM users 
            WHERE token="' . $token . '"';
        return $this->db->query($query)->fetchObject()->id;
    }

    public function getLoggedUsers() {
        $query = '
            SELECT id,name 
            FROM users 
            WHERE token IS NOT NULL  AND token<>""';
        return $this->getArray($query);
    }

    public function addUser($login, $password, $name) {
        $query = '
            INSERT INTO users (login, password, name) 
            VALUES ("' . $login . '","' . $password . '","' .  $name . '")';
        $this->db->query($query);
    }

    public function updateToken($id, $token) {
        $query = '
            UPDATE users 
            SET token="' . $token . '" 
            WHERE id=' . $id;
        $this->db->query($query);
        return true;
    }

    ////////////////////////////////////////
    //////////////forMessages///////////////
    ////////////////////////////////////////

    public function addMessage($user, $message, $messageTo) {
        $query = '
                INSERT INTO messages (userId, message, messageTo) 
                VALUES (' . $user . ',"' . $message . '", ' .  $messageTo . ')
            ';
        $this->db->query($query);
        return true;
    }

    public function getMessages($user) {
        $query = '
                    SELECT m.id, u.name as name, m.message as message, m.messageTo
                    FROM messages as m JOIN users AS u ON u.id=m.userId 
                    WHERE (userId=' . $user . ' or messageTo is NULL or messageTo=' . $user . ') ORDER BY m.id
                ';
        return $this->getArray($query);
    }


    ////////////////////////////////////////
    //////////////forMap////////////////////
    ////////////////////////////////////////
    public function getMap($id) {
        return $this->selectWithCondition('Maps', 'ground, plants, trees', 'id', $id)->fetchObject();
    }

    public function getUnitsTypes() {
        $query = '
                SELECT * 
                FROM unitsTypes
            ';
        return $this->getArray($query);
    }


    ////////////////////////////////////////
    //////////////forCastles////////////////
    ////////////////////////////////////////

    public function addCastle($userId, $castleX, $castleY, $nextRentTime) {
        $query = '
                INSERT INTO gamers (userId, castleX, castleY, nextRentTime) 
                VALUES (' . $userId . ',' . $castleX . ',' . $castleY . ',' . $nextRentTime . ')
            ';
        $this->db->query($query);
        return true;
    }

    public function getCastle($id) {
        //return $this->selectWithCondition('gamers', 'id, castleX as posX, castleY as posY, money, nextRentTime', 'userId', $id)->fetchObject();
        $query = 'SELECT id, castleX as posX, castleY as posY, money, nextRentTime FROM gamers WHERE id='.$id;
        return $this->db->query($query)->fetchObject();
    }

    public function getCastles() {
        $query = '
                SELECT g.id as id, u.name as ownerName, g.castleLevel as Level, g.castleX as posX, g.castleY as posY
                FROM gamers as g 
                JOIN users as u ON g.userId=u.id
            ';
        return $this->getArray($query);
    }

    public function getCastlesRents() {
        $query = '
                SELECT id, money, nextRentTime FROM gamers
            ';
        return $this->getArray($query);
    }

    public function castleLevelUp($gamerId) {
        $query = '
                UPDATE gamers SET 
                castleLevel=castleLevel + 1   
                WHERE id=' . $gamerId;
        $this->db->query($query);
        return true;
    }

    public function getMoney($gamerId) {
            $query = '
            SELECT money 
            FROM gamers 
            WHERE id=' . $gamerId;
            return $this->db->query($query)->fetchObject()->money;
        }

    public function updateMoney($gamer, $money) {
        $query = '
            UPDATE gamers 
            SET money=money+' . $money . ' 
            WHERE id=' . $gamer;
        $this->db->query($query);
        return true;
    }


    public function destroyCastle($id) {
        $query = 'DELETE FROM gamers
        WHERE id=' . $id;
        $this->db->query($query);
        return true;
    }

    public function updateNextRentTime($gamer,$time){
        $query = 'UPDATE gamers SET nextRentTime='. $time . ' WHERE id='. $gamer;
        $this->db->query($query);
        return true;
   }

    ////////////////////////////////////////
    //////////////forVillages///////////////
    ////////////////////////////////////////
    public function createVillage($name, $posX, $posY, $time) {
        $query = 'INSERT INTO villages (name, posX, posY, nextUpdateTime) 
        VALUES ("' . $name . '", ' . $posX . ',' . $posY . ', ' . $time . ')';
        $this->db->query($query);
        return true;
    }

    public function getVillage($id) {
        $query = 'SELECT id, money, population FROM villages WHERE id='.$id;
        return $this->db->query($query)->fetchObject();
    }

    public function getVillages() {
        $query = 'SELECT * FROM villages';
        return $this->getArray($query);
    }

    public function updateVillage($id, $money, $level, $population, $time){
        $query = 'UPDATE villages SET money ='. $money .
            ', level ='. $level .
            ', population ='. $population .
            ', nextUpdateTime ='. $time . ' WHERE id ='. $id;
        $this -> db -> query($query);
        return true;
    }

    public function updateVillagePopulations($id,$population){
        $query = 'UPDATE villages SET population ='. $population . ' WHERE id ='.  $id;
        $this -> db -> query($query);
        return true;
    }

    public function robVillage($id, $money) {
        $query = 'UPDATE villages SET
            money=money - ' . $money . '
            WHERE id=' . $id;
        $this->db->query($query);
        return true;
    }

    public function destroyVillage($id) {
        $query = 'DELETE FROM villages WHERE id=' . $id;
        $this->db->query($query);
        return true;
    }
    ////////////////////////////////////////
    //////////////forUnits//////////////////
    ////////////////////////////////////////

    public function addUnit($gamer, $unit, $hp, $posX, $posY) {
        $query = '
            INSERT INTO units (gamerId, type, hp, posX, posY) 
            VALUES ('.$gamer.', '.$unit.', '.$hp.', '.$posX.', '.$posY.')';
        $this->db->query($query);
        return true;
    }

    public function getUnitTypeData($unitType) {
        $query = '
            SELECT cost, hp
            FROM unitsTypes 
            WHERE id=' . $unitType;
        return $this->db->query($query)->fetchObject();
    }

    public function getUnitsInCastle($castleId){
        $query = '
        SELECT id, type, hp, posX, posY, status, direction 
        FROM units
        WHERE status="inCastle" and gamerId='.$castleId;
    return $this->getArray($query);
    }

    public function getUnits() {
        $query = '
            SELECT id, gamerId as ownerId, type, hp, posX, posY, status, direction 
            FROM units
            ORDER BY gamerId';
        return $this->getArray($query);
    }

    public function getUnit($unitId) {
        $query = '
        SELECT hp
        FROM units 
        WHERE id=' . $unitId;
    return $this->db->query($query)->fetchObject();
    }

    public function getGamerUnits($gamerId) {
        $query = '
        SELECT id, type, status
        FROM units 
        WHERE gamerId=' . $gamerId;
    return $this->getArray($query);
    }

    // По id отдельного юнита меняет у него 
    // hp, posX, posY, status, direction в БД
    public function updateUnit($gamerId, $unitId,$hp, $posX, $posY, $status, $direction){
        $query = '
            UPDATE units 
            SET hp='. $hp. ',posX='. $posX. ',posY='. $posY. ',status="'. $status. '",direction='. $direction. ' 
            WHERE id=' .$unitId. ' AND gamerId=' .$gamerId;
        $this->db->query($query);
        return $query;
    }
    public function updateUnitHP($unitId,$hp){
        $query='UPDATE units
            SET  hp='. $hp . ' WHERE id='. $unitId;
        $this->db->query($query);
        return true;
    }

    public function deadUnits (){
        $query = 'DELETE FROM units WHERE hp <='. 0;
        $this->db->query($query);
        return true;
    }

    ////////////////////////////////////////
    //////////////forGamers/////////////////
    ////////////////////////////////////////
    public function getGamer($userId) {
        $query = '
            SELECT id, castleLevel as level, castleX as posX, castleY as posY, money, nextRentTime  
            FROM gamers 
            WHERE userId=' . $userId;
        return $this->db->query($query)->fetchObject();
    }

    /* About statuses */
    public function getStatuses() {
        $query = 'SELECT * FROM statuses';
        return $this->db->query($query)->fetchObject();
    }

    public function getChatHash() {
        $query = 'SELECT chatHash FROM statuses';
        return $this->db->query($query)->fetchObject()->chatHash;
    }

    public function setChatHash($hash) {
        return $this->simpleUpdate('statuses', 'chatHash', $hash);
    }

    public function setMapTimeStamp($time) {
        return $this->simpleUpdate('statuses', 'mapTimeStamp', $time);
    }

    public function setMapHash($hash) {
        $this->cache->delete('castles');
        $this->cache->delete('villages');
        return $this->simpleUpdate('statuses', 'mapHash', $hash);
    }

    public function setUnitsHash($hash) {
        $this->cache->delete('units');
        return $this->simpleUpdate('statuses', 'unitsHash', $hash);
    }
}
