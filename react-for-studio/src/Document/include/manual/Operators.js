import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';

// import Operator from './Operator';
import OperatorGroup from './Operator/OperatorGroup';

@inject('docsStore')
@observer
class Operators extends Component {
    getOperatorGroup() {
        const data = this.props.docsStore.getData,
            returnData = [];
        console.log('Operators', data.operators);
        _.forEach(data.operators, (value, key) => {
            returnData.push(
                <OperatorGroup key={`m_os_${key}`} dKey={key} opArr={value} />
            );
        });

        return returnData;
    }
    render() {
        return <>{this.getOperatorGroup()}</>;
    }
}

export default Operators;
