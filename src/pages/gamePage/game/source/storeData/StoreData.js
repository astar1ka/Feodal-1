import gamer from '../../../../../store/features/gamer/gamer';
import store from '../../../../../store/store';
import Phaser, { Cameras } from "phaser";

export default function StoreData(scene) {
    let lastUI=false;
    const StoreData = setInterval(()=>{
        let units = store.getState().gamer.units;
        const village = store.getState().village;
        if (village.attacked) {
            scene.villagesGroup.getChildren().find(el => el.id === village.id).attackOnVillage();
            setTimeout(()=>scene.store.loadToStore({attacked: false}, 'village'), 100);
        }
        let ui = store.getState().interface.value.castle;
        if (ui){
            if (ui!=lastUI){
                lastUI=true;
                let myCastle = scene.castlesGroup.getChildren().find(el => el.type === "myCastle");
                if (myCastle) {
                scene.cameras.main.centerOn(myCastle.x,myCastle.y);
                scene.cameras.main.viewScreenUpdate();
                myCastle.select();
                }
            }
        } else {
            lastUI = false;
        }
        units.forEach((unit) => {
            if(unit.status==="outOfCastle") {
                let castle = scene.castlesGroup.getChildren().find(el => el.id === scene.player.id);
                let unitInGame = castle.units.getChildren().find(el => el.hp === unit.hp);
                if (unitInGame) {
                    unitInGame.outCastle();
                }
            }
        })
    }, 100)

    return StoreData;
}