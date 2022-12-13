import Unit from "./Unit"

export default class Soldier extends Unit {
    constructor(scene, unitData) {
        super(scene, unitData)
        this.setTexture('soldier', 0);
        this.anims.create({
            key: "move",
            frames: [{
                key: 'soldier',
                frame: 1,
                duration: 80
            },
            {
                key: 'soldier',
                frame: 2,
                duration: 80
            },
            {
                key: 'soldier',
                frame: 0,
                duration: 80
            }],
            duration: 240,
            repeat: -1
        });
        this.setDisplaySize(40, 70);
    }
}