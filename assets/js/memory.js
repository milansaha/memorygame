/*
* Application Settings
* Global Variables
*/

APP={};
APP.bussy=0;
APP.nextLevel=1;
APP.cardsNumber=2;
APP.cardsSelected=0;
APP.cards={};

APP.start= function (){
    $('.block').hide();
    $('.scoresBox').hide();
    $('#better').hide();
    $('.scoresList').hide();
    
    APP.bussy=1;
    var level=APP.nextLevel;
    APP.nextLevel ++;
    $('#hits').html('0');
    $('#fails').html('0');
    if(level==1){
        $('#points').html('0');
    }
    
    $.getJSON('lib/game-script.php',{'act':'start','level':level},function(json){
        APP.cardsNumber=json.cardsNumber;
        $('#cardsNumber').html(APP.cardsNumber);
        $('#level').html(json.level);
        
        for(var i=1 ; i <=APP.cardsNumber; i++)
        {
            APP.cards[i]=false;
        }
        $('#boxes').html('');
        var width=116*json.showCut;
        $('#boxes').css('width',width);
        var cut=0;
        for(var i=0 ; i <json.playCards; i++)
        {
            cut++;
            link= $('<a>');
            link.attr('href',"javascript:void(0)");
            link.addClass('imagebox');
            link.addClass('back'+json.back);
            link.attr('data-id',i);
            $('#boxes').append(link);
            if (cut == json.showCut)
            {
                cut=0;
                var clear=$('<div class="clear"></div>');
                $('#boxes').append(clear);
            }
        }
    APP.cardsSelected=0;    
    APP.bussy=0;
    });
   
}


$(document).ready(function(){

    APP.start();   

    $('.imagebox').live('click',function(){
        if (APP.cardsSelected<APP.cardsNumber)
            {
                var link=$(this)
                for(var i=1 ; i <=APP.cardsNumber; i++)
                {
                    if(APP.cards[i] && link.attr('data-id')==APP.cards[i].attr('data-id'))
                    {return false;}
                }
                
                APP.cardsSelected = APP.cardsSelected +1;
                APP.cards[APP.cardsSelected]=link;
                var id =link.attr('data-id');
                $.getJSON('lib/game-script.php',{'id':id,'act':'select'} ,function(json){
                    var img = json.image;
                    link.flip({
                        direction:'lr',
                        speed:100,
                        onEnd: function(){
                            link.removeAttr('style');
                            link.css('background','url(assets/images/'+img+'.jpg) no-repeat center center');
                            if(APP.cardsSelected ==APP.cardsNumber && APP.bussy==0)
                            {
                                APP.bussy=1;
                                var params={}
                                params.act='check';
                                params.cards={};
                                for(var i=1 ; i <=APP.cardsNumber; i++)
                                {
                                    params.cards[i]=APP.cards[i].attr('data-id');
                                }

                                $.getJSON('lib/game-script.php', params, function(json){
                                    
                                    $('#points').html(json.score);
                                    
                                    if (json.match==0)
                                    {
                                        $('#fails').html(json.failCount);
                                        window.setTimeout(APP.backCards,500);
                                    }
                                    else
                                    {
                                        $('#hits').html(json.matchCount);
                                        for(var i=1 ; i <=APP.cardsNumber; i++)
                                        {
                                            APP.cards[i].html('<img class="ok" src="assets/ok.png" />');
                                        }
                                        //$('.ok').blink(300);
                                        
                                        if(json.finish==1){
                                            window.setTimeout(APP.showCards,1500);
                                        }    
                                        else if(json.finish==0){
                                            window.setTimeout(APP.deleteCards,1500);
                                        }    
                                        else{
                                            return false; //errror
                                        }
                                        
                                    }
                                })

                            }
                        }
                    });
                });
            }
    });
})


APP.showCards =function()
{
    $('.ok').stopBlink();
    $('.ok').remove();
    $.getJSON('lib/game-script.php',{'act':'getAll'},function(json){
        for(var i=0;i<json.playCards;i++)
            {
                var link =$('.imagebox[data-id='+i+']');
                link.css('background','url(assets/images/'+json.grid[i]+'.jpg) no-repeat center center');
                link.css('visibility','visible');
            }
            var params={};
            
            params.act='checkScore';
            $.getJSON('lib/game-script.php', params, function(json){
                if (json.betterScore){
                    $('.block').show();
                    $('.scoresBox').show();
                    $('#better').show();
                    $('.scoresList').hide();
                }
                else{
                    APP.showScoresList(json.scores);
                }    
            });
            
            
    })
}

APP.deleteCards=function()
{
    $('.ok').stopBlink();
    $('.ok').remove();
    for(var i=1 ; i <=APP.cardsNumber; i++)
    {
        if (APP.cards[i])
        {
            APP.cards[i].css('visibility','hidden');
            APP.cards[i]=false;
        }
    }
    APP.cardsSelected=0;
    APP.bussy=0;
    
}
APP.backCards=function()
{
    if(APP.cardsSelected==APP.cardsNumber)
    {
        APP.revertFlip(1);
    }
}
APP.revertFlip= function (i){
    if (i>APP.cardsNumber)
    {
        APP.cardsSelected=0;
        APP.bussy=0;
    } 
    else
    {
        if (APP.cards[i])
        {
            APP.cards[i].flip({
                direction:'rl',
                speed:100,
                onEnd: function(){
                    APP.cards[i].removeAttr('style');
                    APP.cards[i]=false;
                    APP.revertFlip(i+1);
                }
            });
        }    
    }
}
APP.saveName= function(){
    var name=$('#userName').val();
    var params ={};
    params.act='saveName';
    params.name=name;
    $.getJSON('lib/game-script.php', params, function(json){
        APP.showScoresList(json.scores);
    });
}
APP.showScoresList=function(scores){
    $('#listOfScores').html('');
    for(var i=0 ; i<5;i++)
        {
            if(scores[i])
            {
                var li = $('<li>');
                var label = $('<label>');
                var span = $('<span>');
                span.html(scores[i]['score']);
                label.html(scores[i]['name'])
                li.append(label);
                li.append(span);
                $('#listOfScores').append(li);
            }
        }
    var gameLevel=APP.nextLevel -1;   
    $('#levelInfo').html(gameLevel);    
    $('.block').show();
    $('.scoresBox').show();
    $('#better').hide();
    $('.scoresList').show();
    
}