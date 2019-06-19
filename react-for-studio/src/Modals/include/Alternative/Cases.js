import React from 'react';
import Case from './Case';

const Cases = ({ cases, len, addOpts }) =>
    cases.map((row, i) => (
        <Case
            data={row}
            index={i}
            len={len}
            addOpts={addOpts}
            key={`alter_case_${i}`}
        />
    ));

export default Cases;
