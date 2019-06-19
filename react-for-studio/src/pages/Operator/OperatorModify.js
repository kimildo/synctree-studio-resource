import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link, withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
import OperatorInput from './include/OperatorInput';
@withRouter
@inject('navStore', 'operatorStore')
@observer
class OperatorModify extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Operator', 'Modify']);
        if (!this.props.operatorStore.getOperators) {
            this.props.operatorStore.loadOperators();
        }
    }

    render() {
        const opId = this.props.match.params.op_id;
        return (
            <>
                <Helmet>
                    <title>Operator Modify - Synctree Studio V2.0 </title>
                </Helmet>
                <OperatorInput type={`Modify`} opId={opId} />
            </>
        );
    }
}

export default OperatorModify;
