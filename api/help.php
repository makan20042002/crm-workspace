<?php
declare(strict_types=1);
require dirname(__DIR__).'/v10-lib.php';
require_login();

function help_inline(string $text): string {
    $html=e($text);
    $html=preg_replace('/`([^`]+)`/u','<code>$1</code>',$html)??$html;
    $html=preg_replace('/\*\*([^*]+)\*\*/u','<strong>$1</strong>',$html)??$html;
    return $html;
}
function help_cells(string $line): array {
    $line=trim($line);
    return array_map('trim',explode('|',trim($line,'|')));
}
function help_markdown(string $source): string {
    $lines=preg_split('/\R/u',$source)?:[];$out=[];$paragraph=[];$list='';$inCode=false;$code=[];$section=0;
    $flushParagraph=function()use(&$paragraph,&$out):void{if($paragraph){$out[]='<p>'.help_inline(implode(' ',$paragraph)).'</p>';$paragraph=[];}};
    $closeList=function()use(&$list,&$out):void{if($list!==''){$out[]='</'.$list.'>';$list='';}};
    for($i=0,$count=count($lines);$i<$count;$i++){
        $line=rtrim($lines[$i]);
        if(str_starts_with(trim($line),'```')){if($inCode){$out[]='<pre><code>'.e(implode("\n",$code)).'</code></pre>';$code=[];$inCode=false;}else{$flushParagraph();$closeList();$inCode=true;}continue;}
        if($inCode){$code[]=$line;continue;}
        if(preg_match('/^(#{1,6})\s+(.+)$/u',$line,$m)){$flushParagraph();$closeList();$level=strlen($m[1]);$section++;$id=($level===2&&(preg_match('/^(?:9\.|۹\.)/u',$m[2])||stripos($m[2],'troubleshooting')!==false||str_contains($m[2],'رفع اشکال')))?'troubleshooting':'section-'.$section;$out[]='<h'.$level.' id="'.$id.'">'.help_inline($m[2]).'</h'.$level.'>';continue;}
        if($line!==''&&str_contains($line,'|')&&isset($lines[$i+1])&&preg_match('/^\s*\|?(?:\s*:?-{3,}:?\s*\|)+\s*:?-{3,}:?\s*\|?\s*$/',$lines[$i+1])){$flushParagraph();$closeList();$headers=help_cells($line);$i+=2;$rows=[];while($i<$count&&trim($lines[$i])!==''&&str_contains($lines[$i],'|')){$rows[]=help_cells($lines[$i]);$i++;}$i--;$html='<div class="help-table-wrap"><table><thead><tr>';foreach($headers as$cell)$html.='<th>'.help_inline($cell).'</th>';$html.='</tr></thead><tbody>';foreach($rows as$row){$html.='<tr>';foreach($headers as$n=>$_)$html.='<td>'.help_inline((string)($row[$n]??'')).'</td>';$html.='</tr>';}$out[]=$html.'</tbody></table></div>';continue;}
        if(preg_match('/^\s*[-*]\s+(.+)$/u',$line,$m)){$flushParagraph();if($list!=='ul'){$closeList();$list='ul';$out[]='<ul>';}$item=$m[1];if(preg_match('/^\[([ xX])\]\s*(.*)$/u',$item,$check))$out[]='<li class="help-check"><span>'.(trim($check[1])===''?'☐':'☑').'</span> '.help_inline($check[2]).'</li>';else$out[]='<li>'.help_inline($item).'</li>';continue;}
        if(preg_match('/^\s*\d+[.)]\s+(.+)$/u',$line,$m)){$flushParagraph();if($list!=='ol'){$closeList();$list='ol';$out[]='<ol>';}$out[]='<li>'.help_inline($m[1]).'</li>';continue;}
        if(trim($line)===''){$flushParagraph();$closeList();continue;}
        $paragraph[]=trim($line);
    }
    if($inCode)$out[]='<pre><code>'.e(implode("\n",$code)).'</code></pre>';$flushParagraph();$closeList();return implode("\n",$out);
}
function help_docs(): array {
    $dir=dirname(__DIR__).'/docs';$guides=[];
    foreach(glob($dir.'/*.md')?:[]as$file){$name=basename($file);if(!preg_match('/^([A-Za-z0-9_-]+)\.(en|fa)\.md$/',$name,$m))continue;$source=(string)file_get_contents($file);preg_match('/^#\s+(.+)$/m',$source,$title);$key=$m[1];$guides[$key]['key']=$key;$guides[$key]['languages'][$m[2]]=['title'=>trim((string)($title[1]??$key)),'file'=>$name];}
    ksort($guides,SORT_NATURAL|SORT_FLAG_CASE);return array_values($guides);
}
$action=(string)($_GET['action']??'list');
if($action==='list')json_response(['ok'=>true,'guides'=>help_docs()]);
if($action==='render'){
    $guide=(string)($_GET['guide']??'AI-GUIDE');$lang=clean_status((string)($_GET['lang']??'fa'),['fa','en'],'fa');
    if(!preg_match('/^[A-Za-z0-9_-]+$/',$guide))json_response(['ok'=>false,'error'=>'invalid_guide'],422);
    $path=dirname(__DIR__).'/docs/'.$guide.'.'.$lang.'.md';if(!is_file($path))json_response(['ok'=>false,'error'=>'guide_not_found'],404);
    $source=(string)file_get_contents($path);json_response(['ok'=>true,'guide'=>$guide,'lang'=>$lang,'html'=>help_markdown($source)]);
}
json_response(['ok'=>false,'error'=>'unknown_action'],404);
