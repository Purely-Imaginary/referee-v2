// haxball build QnHwsaqi

class Writer {
    round(value) {
        const coeff = 1000;
        return Math.round(value * coeff) / coeff;
    }
    constructor() {
        this.pos = 0;
        this.view = new DataView(new ArrayBuffer());
    }

    resize(size) {
        if (size !== undefined || this.view.byteLength < this.pos) {
            if (size === undefined) {
                size = Math.max(this.pos, 2 * this.view.byteLength);
            }
            const buffer = new ArrayBuffer(size);
            (new Uint8Array(buffer)).set(new Uint8Array(this.view.buffer.slice(0, size)));
            this.view = new DataView(buffer);
        }
    }

    compact() {
        this.resize(this.pos);
    }

    writeUint8(value) {
        const pos = this.pos;
        this.pos += 1;
        this.resize();
        this.view.setUint8(pos, value);
    }

    writeFloat64(value) {
        const pos = this.pos;
        this.pos += 8;
        this.resize();
        this.view.setFloat64(pos, value, false)
    }

    writeBool(value) {
        this.writeUint8(value ? 1 : 0);
    }

    writeDisc(disc) {
        this.writeFloat64(disc._a.x);
        this.writeFloat64(disc._a.y);
        this.writeFloat64(disc._M.x);
        this.writeFloat64(disc._M.y);
    }

    writeDiscJSON(disc) {
        let output = {};
        output['a'] = {};
        output['b'] = {};
        // console.log(disc);

        output['a']['x'] = this.round(disc.a.x);
        output.a.y = this.round(disc.a.y);
        output.b.x = this.round(disc.D.x);
        output.b.y = this.round(disc.D.y);
        return output;
    }

    writeBuffer(buffer) {
        const pos = this.pos;
        this.pos += buffer.byteLength;
        this.resize();
        (new Uint8Array(this.view.buffer)).set(new Uint8Array(buffer), pos);
    }

    // writePython(controller) {
    //     const L = controller._L;
    //     this.writeFloat64(controller._S * controller._vc); // replay time
    //     if (!L._H) {
    //         this.writeUint8(0); // 0: menu
    //     } else {
    //         this.writeUint8(L._H._Ga > 0 ? 1 : L._H._zb == 0 ? 2 : L._H._zb == 1 ? 3 : 4); // 1: pause, 2: warmup, 3: game, 4: goal
    //         this.writeFloat64(L._H._Ac); // game time
    //         this.writeUint8(L._H._Kb); // red score
    //         this.writeUint8(L._H._Cb); // blue score
    //         this.writeBool(L._H._xa > 0 && L._H._Ac > L._H._xa); // overtime
    //         const players = L._D.filter(x => x._$._P !== 0);
    //         this.writeUint8(players.length); // player count
    //         for (const player of players) {
    //             this.writeUint8(player._T); // id
    //             this.writeUint8(player._mb); // input
    //             this.writeBool(player._bc); // kick
    //             this.writeUint8(player._$._P - 1); // team, 0: red, 1: blue
    //             this.writeDisc(player._F); // player disc
    //         }
    //         this.writeDisc(L._H._wa._K[0]); // ball disc
    //     }
    // }

    writeJSON(controller) {
        const L = controller._L;
        let tick = {};
        tick['rT'] = this.round(controller._S * controller._vc) // replay time
        if (!L._H) {
            tick['s'] = 0; // 0: menu
        } else {
            tick['s'] = L._H._Ga > 0 ? 1 : L._H._zb == 0 ? 2 : L._H._zb == 1 ? 3 : 4; // 1: pause, 2: warmup, 3: game, 4: goal
            tick['gT'] = this.round(L._H._Ac); // game time
            tick['rS'] = L._H._Kb; // red score
            tick['bS'] = L._H._Cb; // blue score
            tick['o'] = (L._H._xa > 0 && L._H._Ac > L._H._xa); // overtime
            const players = L._D.filter(x => x._$._P !== 0);
            tick['pC'] = (players.length); // player count
            tick['p'] = {}
            for (const player of players) {
                tick['p'][player._T] = {}
                tick['p'][player._T]['id'] = (player._T); // id
                tick['p'][player._T]['in'] = (player._mb); // input
                tick['p'][player._T]['k'] = (player._bc); // kick
                tick['p'][player._T]['t'] = (player._$._P - 1); // team, 0: red, 1: blue
                tick['p'][player._T]['d'] = this.writeDiscJSON(player._F); // player disc
            }
            tick['b'] = this.writeDiscJSON(L._H._wa._K[0]); // ball disc
        }
        return tick;
    }
}

if (typeof module !== 'undefined') {
    module.exports.Writer = Writer;
}
