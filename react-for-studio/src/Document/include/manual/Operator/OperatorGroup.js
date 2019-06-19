import React, { Component } from 'react';
import { toJS } from 'mobx';
import _ from 'lodash';

import OperatorEach from './OperatorEach';

class OperatorGroup extends Component {
    getBody() {
        const { opArr } = this.props,
            datas = Object.values(toJS(opArr));
        return datas.map(data => {
            return data.map(d => (
                <OperatorEach key={`docBody_${d.op_id}`} d={d} />
            ));
        });
    }

    render() {
        const { dKey } = this.props,
            namespace = dKey.split('_')[1];

        return (
            <>
                <section className="docBody_Fname">
                    <div className={'leftBody_Fname'}>
                        <h2>{namespace}</h2>
                    </div>
                </section>
                {this.getBody()}
            </>
        );
    }
}

export default OperatorGroup;
