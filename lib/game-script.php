<?php
set_time_limit(30);
session_start();

$act    = ($_GET['act'])?$_GET['act']:false;
$id     = (intval($_GET['id']))?intval($_GET['id']):false;
$cards  = (isset($_GET['cards']) && is_array($_GET['cards']))?$_GET['cards']:array();
$level  = (intval($_GET['level']))? intval($_GET['level']): 1;
$name   = ($_GET['name'])?$_GET['name']:false;

$game = Game::getInstance();

switch ($act)
{
    case 'select':
        $result['image']= $game->getImage($id); 
        break;
    case 'check':
        $result['match']=$game->checkCards($cards);
        $result['matchCount']=$game->getMatchCount();
        $result['failCount']=$game->getFailsCount();
        $result['score']=$game->getScore();
        $result['finish']=$game->isFinish();
        break;
    case 'checkScore' :
        $result['betterScore']=$game->checkScore();
        $result['scores']=$game->getLevelScores();
        break;
    case 'getAll':

        if ($game->isFinish())
        {
            $result=$game->getLevelInfo();
            $result['grid']=$game->getGrid();
            $result['match']=$game->getMatchCount();
        }
        break;
    case 'saveName':
        $result['scores']=$game->saveUser($name);
        break;
    case 'start':
        $game->setLevel($level);
        $result=$game->getLevelInfo();
        $game->startGame();
        break;
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($result);
die;




class Game{
    private $levelScores=array();
    private $score=0;
    private $match=0;
    private $fails=0;
    private $grid=array();
    private $level=1;
    private $combinations=0;
    private $finish=0;
    
    private function _construct(){}
    
    public static function getInstance()
    {
        if (!isset($_SESSION['GAME']))
        {
            $_SESSION['GAME']= new Game();
        }
        return $_SESSION['GAME'];
    }
    
    /**
     *
     * @param int $id 
     * @return int
     */
    public function getImage($id)
    {
        return $this->grid[$id];
    }
    
    
    /**
     *
     * @param array $cards
     * @return int 
     */
    public function checkCards($cards)
    {
        if ($this->isFinish()) // if this happend the check is call when the game is over.
        {return -1;}
        
        $result=1;
        $bean=$this->grid[$cards[1]];    
        
        foreach($cards as $card)
        {
            if ($this->grid[$card]!=$bean)
            {$result=0;}
        }
        
        if($result==1)
        {
            $this->match ++;
            // my magic formula to calculate the score :)
            
            $points=intval($this->match*5) - $this->fails;
            
            $this->score += intval($points);
        }
        else 
        {$this->fails ++;}
        
        
        if ($this->match ==$this->combinations)
        {
            $this->finish=1;
        }
        
        return $result;
    }
    
    /**
     *
     *  Starts the game
     */
    public function startGame()
    {
        $this->finish=0;
        $this->fails=0;
        $this->match=0;
        
        if($this->level==1) // the first level set the score to 0
        {$this->score=0;}
        
        $this->grid=$this->getStartValues();
        

    }
    
    /**
     *
     * set starts values in grid
     * @return array
     */
    private function getStartValues()
    {
        $levelInfo=$this->getLevelInfo($this->level);
        $grid=array();
        $gridCount=array();
        for ($i=0;$i<$levelInfo['playCards'];$i++)
        {
            $grid[$i]=$this->getValidValue($gridCount,$levelInfo);
            $gridCount[$grid[$i]]++;
        }
        return $grid;
    }
    
    
    /**
     * 
     * get a non duplicate value to form the grid.
     *
     * @param array $gridCount
     * @param array $levelInfo
     * @return int 
     */
    private function getValidValue($gridCount,$levelInfo)
    {
        $value=false;
        $range=$levelInfo['playCards']/$levelInfo['cardsNumber'];
        while(!$value)
        {
            $tmpVal=rand(1,$range);
            if (!isset($gridCount[$tmpVal]) || ($gridCount[$tmpVal] <$levelInfo['cardsNumber']) )
            {$value=$tmpVal;}
        }
        return $value;
    }
    
    
    
    
    /**
     *
     * returns the information about the level
     * 
     * @return array
     */
    public function getLevelInfo()
    {
        $level=array();
        
        $ln=1; // level starts in 1
        $level[$ln]['level']=$ln;       //level
        $level[$ln]['cardsNumber']=2;   //cards to play
        $level[$ln]['playCards']=24;     //number of cards in play
        $level[$ln]['showCut']=6;       //show cards by ..
        $level[$ln]['back']=1;         //cards styles

        
        $levelNumber =intval($this->level);
        if (isset($level[$levelNumber]))
        {
            return $level[$levelNumber];
        }
        else
        {
            return $level[$ln];
        }
        
    }
    
    /**
     * @result int
     */
    public function checkScore()
    {
       $result=0; 
       if ($this->isFinish())
       {
           $score= new Scores();
           $result=$score->isBetterScore($this->level, $this->score);
       }
       return $result;
    }
    
    
    public function getLevelScores()
    {
           $score= new Scores();
           return $score->getScores($this->level);
    }
    
    public function saveUser($name)
    {
        if ($this->checkScore())
        {
           $this->finish=0; 
           $score= new Scores();
           $score->save($name, $this->score, $this->level);
           return $score->getScores($this->level);
        }
    }
    
    /**
     *
     * @param int $level 
     */
    public function setLevel($level)
    {
        $this->level=$level;
        $levelInfo=$this->getLevelInfo();
        $this->combinations=$levelInfo['playCards']/$levelInfo['cardsNumber'];
    }
    
    /**
     *
     * @return int
     */
    public function getMatchCount()
    {
        return $this->match;
    }
    /**
     *
     * @return int
     */
    public function getFailsCount()
    {
        return $this->fails;
    }
    /**
     *
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }
    
    /**
     * 
     * @return int
     */
    public function isFinish()
    {
        return $this->finish;
    }
    
    /**
     * 
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }
    /**
     * 
     * @return array
     */
    public function getGrid()
    {
        return $this->grid;
    }
}


class Scores
{
    private $connect=false;
    
    public function __construct() {
        $this->connect = sqlite_open("game.sqlite");
        /*
        $sql='CREATE TABLE "scores" ("id" integer primary key  unique , "date" DATETIME DEFAULT CURRENT_DATE, "name" VARCHAR(20) NOT NULL , "score" INTEGER, "level" INTEGER)';
        sqlite_query($sql, $connect);
        */
    }
    
    public function getScores($level)
    {
        $sql="select name,score,date from scores where level=".$level.' order by score desc limit 5';
        $result=sqlite_query($sql, $this->connect);
        return sqlite_fetch_all($result);
    }
    
    public function isBetterScore($level,$userScore)
    {
        $scores=$this->getScores($level);

        if(empty ($scores))
        {return 1;}
		
		if (count ($scores)<5)
		{return 1;}
        
        foreach($scores as $score)
        {
            if ($score['score']<$userScore)
            {
                return 1;
            }
        }
        return 0;
    }
            
    public function save($name,$score,$level)
    {
        $name=trim($name);
        if ($name=='')
        {return false;}
         
        $name=substr($name, 0,15);
        
        $name=mysql_escape_string($name);
        $score=intval($score);
        $level=intval($level);
        $date=date('y-m-d');
        $sql = "insert into scores values (null,'$date','$name','$score','$level')";
        sqlite_query($sql, $this->connect);
    }
}
