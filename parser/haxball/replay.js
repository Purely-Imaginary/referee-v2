'use strict';

const fs = require('fs');

const hbr21 = require('./hbr2-1');
const hbr22 = require('./hbr2-2');
const hbr23 = require('./hbr2-3');
const Writer = require('./writer').Writer;

function getClosure(replay) {
  const view = new DataView(replay.buffer);
  if (view.getUint32(0) === 1212305970) {
    const v = view.getUint32(4);
    if (v === 1) {
      return hbr21.closure;
    } else if (v === 2) {
      return hbr22.closure;
    } else if (v === 3) {
      return hbr23.closure;
    } else {
      throw new Error('unknown version');
    }
  } else {
    throw new Error('not HBR2');
  }
}

function convert(replay) {
  const exposed = getClosure(replay)();

  const controller = new exposed.$_ReplayController(replay, new exposed.$_GameState, exposed.v);
  const writer = new Writer()
  const names = new Object();
  const tick = [];
  const temp = [];
  controller.onTick = () => {
    // return;

    for (const player of controller._L._D.filter(x => x._$._P !== 0)) {
      names[player._T] = player._o;
    }
    let single = writer.writeJSON(controller)
    if (single.s !== 0) {
      tick.push(single);
    }
  };

  controller.onTick();
  controller._td = controller._Te * controller._Mg;
  while (controller._td > 0) {
    controller._v();
  }
  
  writer.compact();
  const writer2 = new Writer();
  let payload = {names: names, match: tick}
  writer2.writeBuffer(JSON.stringify(payload), 'utf8');
  writer2.compact();
  return JSON.stringify(payload);
}

if (require.main === module) {
  switch (process.argv[2]) {
    case 'convert': {
      const fileFrom = process.argv[3];
      const fileTo = process.argv[4];
      console.log(`${fileFrom} -> ${fileTo}`);
      fs.writeFileSync(fileTo, (convert(new Uint8Array(require('fs').readFileSync(fileFrom)))));
      break;
    }
    case 'help':
    case '--help':
    case '-h':
    default: {
      const prefix = `node ${module.id}`;
      console.log(['convert INPUT OUTPUT', 'help', '--help', '-h'].map(x => `${prefix} ${x}`).join('\n'));
    }
  }
}
