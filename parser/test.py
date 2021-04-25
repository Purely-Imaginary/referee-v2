import replay, os, json, math, sys
import threading

matches = {}
threads = []

def calculateDistance(x1,y1,x2,y2):
    dist = math.sqrt((x2 - x1)**2 + (y2 - y1)**2)
    return dist

def hasVectorChanged(ball1, ball2):
    vector1 = math.atan2(ball1.vx, ball1.vy) * (180/math.pi)
    vector2 = math.atan2(ball2.vx, ball2.vy) * (180/math.pi)
    change = abs(vector1 - vector2)
    return change > 0.5

def threadedAnalysis(path):
    with open(path, "rb") as game:
            print(path + ' starting...', end="")
            bin = replay.Replay(game.read()) 
            time = path[-26:-10].replace('h',':')
            time = time[:10] + ' ' + time[11:]
            meaningfulStates = []
            actualPlayers = {}
            startingGameTime = 0
            matchLengthInTicks = 0
            tickCounter = 0
            goals = []
            score = {
                'red' : 0,
                'blue': 0
            }

            if bin[1][0].gameTime is not None:
                score = {
                    'red': bin[1][0].score[0],
                    'blue': bin[1][0].score[1],
                }
                startingGameTime = bin[1][0].gameTime

            for tick in bin[1]:
                if tick.state.value >= 3:
                    for player in tick.players:
                        if player.id not in actualPlayers:
                            actualPlayers[player.id] = {'team': player.team.name, 'ticks': 0}
                        actualPlayers[player.id]['team'] = player.team.name
                        actualPlayers[player.id]['ticks'] = actualPlayers[player.id]['ticks'] + 1
                    matchLengthInTicks += 1
                    meaningfulStates.append(tick)
                
                if (tick.state.value == 4 and bin[1][tickCounter-1].state.value == 3 and tickCounter != 0):
                    if (bin[1][tickCounter-1].score[0] == tick.score[0] and bin[1][tickCounter-1].score[1] == tick.score[1]): 
                        tickCounter+=1
                        continue
                    goalScorerId = -1
                    goalShotTime = 0
                    # TODO: goalShotSpeed
                    goalSide = "red"
                    if (bin[1][tickCounter-1].score[0] == tick.score[0]):
                        goalSide = "blue"
                    for i in reversed(range(tickCounter-1)):
                        if goalScorerId != -1:
                            break
                        targetTick = bin[1][i]
                        for player in targetTick.players:
                            if player.team.name != goalSide:    
                                continue

                            distanceFromBall = calculateDistance(targetTick.ball.x, targetTick.ball.y, player.disc.x, player.disc.y)
                            if distanceFromBall < 30 and hasVectorChanged(targetTick.ball, bin[1][i+1].ball):
                                goalScorerId = player.id
                                goalShotTime = round(targetTick.gameTime, 3)
                                break
                        
                    goals.append({
                        "goalTime": round(tick.gameTime, 3),
                        "goalScorerName": bin[0][goalScorerId],
                        "goalShotTime": goalShotTime,
                        "goalSide": goalSide,
                        "goalSpeed": round(math.hypot(tick.ball.vy, tick.ball.vx),3),
                        "goalTravelTime": round(tick.gameTime - goalShotTime, 3)
                    })
                    if (goalSide == "red"):
                        score['red'] += 1
                    else:
                        score['blue'] += 1

                tickCounter+=1
            if meaningfulStates.__len__() == 0:
                print(' empty')
                return

            lastState = meaningfulStates[-1:]
            rawPositions = ""
            teams = {'red': [], 'blue': []}
            for player in lastState[0].players:
                rawPositions += str(player.disc.x) + "-" + str(player.disc.y) + "|"

            for playerId in actualPlayers:
                if (actualPlayers[playerId]['ticks'] == 0):
                    continue
                for targetPlayerId in actualPlayers:
                    if playerId != targetPlayerId and bin[0][playerId] == bin[0][targetPlayerId]:
                        actualPlayers[playerId]['ticks'] += actualPlayers[targetPlayerId]['ticks']
                        actualPlayers[targetPlayerId]['ticks'] = 0
                if (actualPlayers[playerId]['ticks'] / matchLengthInTicks > 0.6):
                    teams[actualPlayers[playerId]['team']].append(bin[0][playerId])
                
            saveData = {
                'startingGameTime': round(startingGameTime,3),
                'gameTime': round(lastState[0].gameTime, 3),
                'time': time,
                'teams': teams,
                'goalsData': goals,
                'score': score,
                'rawPositionsAtEnd': rawPositions
            }

            print(' processed')
            s = json.dumps(saveData, default=lambda x: x.__dict__, sort_keys=True, indent=4)
            with open('/var/www/files/replayData/processed/' + path[38:] + '.json', 'w+') as f:
                f.write(s)

# threadedAnalysis("preprocessed/temp")
threadedAnalysis(sys.argv[1])
os.remove(sys.argv[1])

# for subdir, dirs, files in os.walk('preprocessed/'):
#         for file in files:
#             if file.split('.')[-1] != "bin":
#                 continue
#             path = os.path.join(subdir, file)
#             threadedAnalysis(path)