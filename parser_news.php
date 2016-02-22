<?php
//Вставить удаление старых ссылок
//В случае отсутствия ссылки на картинку, заменяем стандартной и не выводим ее в тексте
define('MODX_API_MODE', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/index.php');
require_once '/var/www/u0100174/public_html/gorspravka.org/simplehtmldom/simple_html_dom.php';

class News
{
    public $title, $imagesrc, $text, $link, $tags;
            
    public function getData($rss_link)
    {
        $this->title='';
        $this->imagesrc=''; 
        $this->text=''; 
        $this->link=''; 
        $this->tags=array();
        //загружаем в него данные
        $html = file_get_html($rss_link);
        //находим все ссылки на странице и...

        if ($html->innertext != '' and count($html->find('.article__title'))) {

            /*foreach ($html->find('.article__title') as $a) {
                echo $a;
            }*/
            $tit=$html->find('.article__title',0);//ищем <h1 class = article__title и получаем заголовок новости
            $this->title=$tit->plaintext;
            //echo $title->plaintext;

        }

        if ($html->innertext != '' and count($html->find('img[class=article__image]'))){
            $image = $html->find('img[class=article__image]',0);//Вытаскиваем изображение
            $this->imagesrc=$image->src;//выводим ссылку на изображение
        }

        if ($html->innertext != '' and count($html->find('div[class=article__paragraph]'))) {

            foreach ($html->find('div[class=article__paragraph]') as $a) {
                $this->text= $this->text.$a->plaintext.'<br>';//получаем текст новости
            }

        }
        if ($html->innertext != '' and count($html->find('div.resources__item a[class=resources__main j-metrics__clicks-out]'))) {

            $l = $html->find('div.resources__item a[class=resources__main j-metrics__clicks-out]',0);//получаем ссылку на источник
            $this->link = $l->href;
        }
        if ($html->innertext != '' and count($html->find('a[class=j-tags__tag tags__item]'))) {

            foreach ($html->find('a[class=j-tags__tag tags__item]') as $a) {
                $this->tags[]= $a->plaintext;//получаем теги, чтобы знать, в какой раздел добавлять новость
            }

        }
        //освобождаем ресурсы
        $html->clear();
        unset($html);
        return;
    }
}

function parseRss($rss_url)
{
    //$source="http://www.sunhome.ru/xml/rss_image.php";
    //rss ресурс фида
    $document=simplexml_load_file($rss_url); 
    
    if(!file_exists("links.txt")) $fl=  fopen ("links.txt", 'w+');
    else $fl=  fopen("links.txt", 'r+');
    while (!feof($fl)) 
    {
        $links_array[] = fgets($fl);//Читаем из файла все строки с ссылками в массив
    }
    //на этом этапе парсим ресурс, а точнее полученный xml-документ в php объект
    foreach($document->channel->item as $i){ 
    //проходим теперь по объекту циклом

    //$title="$i->title";//Заголовок новости
    //$desc="$i->description";//Краткое содержание
    //если в файле была найдена хоть одна запись с ссылкой
    if (count($links_array)!=0)
    {
            if (!in_array($i->link." ".substr($i->pubDate,0,25)."\r\n", $links_array))//Если найденная ссылка+дата не совпадает ни с одной из ссылок из файла 
            {
                $links[]="$i->link";//Ссылка
                fwrite($fl, $i->link." ".substr($i->pubDate,0,25)."\r\n") or die("Сбой при записи в файл links.txt");
            }
    }
    else 
    {
        $links[]="$i->link";//Ссылка
        fwrite($fl, $i->link." ".substr($i->pubDate,0,25)."\r\n") or die("Сбой при записи в файл links.txt");
    }
    
    //$date=substr($i->pubDate,0,16);//Дата создания
    //на этом этапе мы в соответствующие переменные забиваем необходимые данные
    //echo $i->link;
    /*echo"<h2> $title </h2>";
    //выводим заголовок на экран
    echo"<small>$date</small><br/>";
    //выводим дату
    echo"<p style='color:gray'>$desc</p>";
    //выводим само сообщение
    
    //указываем ссылку на автора
    echo"<hr/><br/>"; 
    //разделяем каждую запись полосой*/
    }
    //echo "rss parser by noted.org.ua ver-0.1<br/>";
    fclose($fl);
    return $links;
}

function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
        
        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
}

function str2url($str) {
    // переводим в транслит
    $str = rus2translit($str);
    // в нижний регистр
    $str = strtolower($str);
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    // удаляем начальные и конечные '-'
    $str = trim($str, "-");
    return $str;
}



$rss_links=parseRss('http://news.rambler.ru/rss/Simferopol/');
/*$str = implode(', ', $rss_links);
$fp = fopen("links.txt", "a"); // Открываем файл в режиме записи

$test = fwrite($fp, $str); // Запись в файл*/
//Инициализируем api modx 
$modx=new modX();
$modx->initialize('web');

$newPost = new News();

// Создаем массив ресурсов


$tags = array(
    "Авто"=>'4978',
    "Бизнес"=>'4979',
    "Общество"=>'4984',
    "Политика"=>'4980',
    "Происшествия"=>'4981',
    "Спорт"=>'4982',
    "Разное"=>'4983'
);
for($i=0; $i<count($rss_links);$i++)
{
    $newResource = $modx->newObject('modResource');
    $newPost->getData($rss_links[$i]);
    // Заполняем нужные значения
    $newResource->set('pagetitle',strip_tags($newPost->title));//сразу удаляем возможные теги
    foreach ($tags as $key=>$value)
    {
        if (!in_array($key, $newPost->tags))//Если найденный тег не совпадает ни с одним из найденных 
        {
           $newResource->set('parent','4983');//Присваиваем родителя категорию "В мире"
        }
        else 
        {
            $newResource->set('parent',$value);
            break;
        }
    }


    
    $newResource->setContent(strip_tags($newPost->text));
    $newResource->set('template','20');
    $newResource->set('createdon', time());
    $newResource->set('published','1');
    $newResource->set('hidemenu','1');
    $newResource->set('alias',str2url(strip_tags($newPost->title)));

    // Сохраняем ресурс
    if ($newResource->save()) {
      //$id = $newResource->get('id');
      if($newPost->imagesrc!='')$newResource->setTVValue('news-image',$newPost->imagesrc);
      else $newResource->setTVValue('news-image','http://gorspravka.org/assets/gallery/default_news.jpg');
      $newResource->setTVValue('src-news',$newPost->link);
      $newResource->save();
    // Очищаем кеш, чтобы изменения были видны сразу
      $modx->cacheManager->refresh();
    }
        /*$newPost->getData($rss_links[$i]);
        echo '<h1>'.$newPost->title.'</h1>';
        echo '<br>';
        echo $newPost->imagesrc;
        echo '<br>';
        echo $newPost->text;
        echo '<br>';
        echo $newPost->link;
        echo '<br>';
        echo implode(', ', $newPost->tags);
        echo '<br><br>';*/
}
/*foreach ($rss_links as $rss_link)
{
    
    $newPost->getData($rss_link);
    echo '<h1>'.$newPost->title.'</h1>';
    echo '<br>';
    echo $newPost->imagesrc;
    echo '<br>';
    echo $newPost->text;
    echo '<br>';
    echo $newPost->link;
    echo '<br>';
    echo implode(', ', $newPost->tags);
    echo '<br><br>';
}*/

