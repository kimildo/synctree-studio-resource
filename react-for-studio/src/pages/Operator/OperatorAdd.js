import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
import OperatorInput from './include/OperatorInput';
@withRouter
@inject('navStore', 'operatorStore')
@observer
class OperatorAdd extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Operator', 'Add']);
        if (!this.props.operatorStore.getOperators) {
            this.props.operatorStore.loadOperators();
        }
    }

    render() {
        return (
            <>
                <Helmet>
                    <title>Operator Add - Synctree Studio V2.0 </title>
                </Helmet>
                <OperatorInput type={`Add`} />
            </>
        );
    }
}

export default OperatorAdd;
