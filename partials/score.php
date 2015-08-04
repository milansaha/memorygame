<div class="container">
    <div class="block"></div>

    <div class="scoresBox">
        <div id="better"  class="dn">
            <h3>Congrats !!! <br/><br/> You are one of best in this level !</h3>
            <div class="scoreInfo">
                <div><label>Enter your name</label></div>
                <input type ="text" MaxLength="15" id="userName" /><input type="button" onclick="GAME.saveName()" value ="OK" />
            </div>
        </div>
        <div class="scoresList dn" >
            <h2>Best Scores in level <span id="levelInfo">0</span></h2>
            <ol id="listOfScores"></ol>
            <a class="next-level" href="javascript:void(0)" onclick="GAME.start()">Next Level</a>
        </div>
    </div>
    
    <div class="outBox">
        <div id ="boxes" class="boxes"></div>
    </div>

    <div class="score">
        <!-- <h1>SCORE</h1> -->
        <!-- <div class="level"> Level <span id="level">1</span></div> -->
        <div>
            <label>Points:</label><span class="points" id="points">0</span>
            <div class="clear"></div>
        </div>
        <div>
            <label>Hits:</label><span class="points" id="hits">0</span>
            <div class="clear"></div>
        </div>
        <div>
            <label>Fails:</label><span class="points" id="fails">0</span>
            <div class="clear"></div>
        </div>
        <div>
            <label>Cards:</label><span class="points" id="cardsNumber">0</span>
            <div class="clear"></div>
        </div>
    </div>
</div>