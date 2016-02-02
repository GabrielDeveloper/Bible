<?php


class Construct
{

    private $pdo;
    public function __construct()
    {
        try{
            $this->pdo = new \PDO('mysql:host=localhost;dbname=bible_api','root', 'root');
            if($this->pdo){
                return $this->pdo;
            }

        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }
    
    public function init($params)
    {
        $this->createUniqueXml();
//        $this->selectVerses($params);
    }

    public function createUniqueXml()
    {
        $dir = __DIR__ . "/biblexml/*.xml";
        $patch = __DIR__ . "/NVI.xml";
        $arquivo = fopen($patch, 'w+');
        
        $header = "<?xml version='1.0' encoding='UTF-8'?>
<usfx xmlns:xsi='http://eBible.org/usfx.xsd' xsi:noNamespaceSchemaLocation='usfx.xsd'>";
        fwrite($arquivo, $header);

        //    var_dump(glob($dir));die;
        foreach (glob($dir) as $file) {
            echo $file . "\n";
            $page = file_get_contents($file);
            $page = str_replace("&nbsp", " ", $page);
            fwrite($arquivo, $page);
        }
        fwrite($arquivo, "</usfx>");
        fclose($arquivo);    
    }

    public function getBooks()
    {
        $sql = "SELECT * from books order by id";
        foreach($this->pdo->query($sql) as $row) {
            $this->selectVerses($row[0]);
        }
     echo "FIM ";
    }

    public function selectVerses($params)
    {
        $array = [];
        $sql = "SELECT * from verses where version = 'nvi' AND book = ".$params[1];
        $chapter = 1;
        $count = 1;

            $array[] = "<book id=\"{$params[2]}\">
    <h>{$params[3]}</h>";
        $array[] = '<c id="1"/>';
        foreach ($this->pdo->query($sql) as $row){
            
            $text = $row["text"];
            $enc = mb_detect_encoding($text, "UTF-8,ISO-8859-1");
            if ($row['chapter'] != $chapter ) {
                $array[] = '<c id="'. $row['chapter'] .'"/>';
                $chapter++;
            }
            $array[] = "<v id=\"{$row["verse"]}\"/>". iconv($enc, "UTF-8", $text). "<ve/>";
        }
                $array[] = "</book>";
                $this->createFile($array, $params);
    }

    public function createFile($array, $params)
    {
        $patch = __DIR__."/{$params[1]}-{$params[3]}.xml";
        $arquivo = fopen($patch, 'w+');
        fwrite($arquivo, implode("\n", $array));
        fclose($arquivo);
    }


}



(new Construct)->init($argv);
