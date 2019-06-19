import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link, withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
import Loading from '../../Loading';
import OperatorLayers from './include/OperatorLayers';
@withRouter
@inject('navStore', 'operatorStore')
@observer
class OperatorList extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Operator', 'Edit']);
        if (!this.props.operatorStore.getOperators) {
            this.props.operatorStore.loadOperators();
        }
    }
    getOperatorLayer() {
        const ops = this.props.operatorStore.getOperators;
        if (!!ops) {
            return <OperatorLayers ops={ops} />;
        }
        return ops;
    }

    render() {
        const ops = this.getOperatorLayer();
        return (
            <>
                <Helmet>
                    <title>Operator - Synctree Studio V2.0 </title>
                </Helmet>
                <div className="biz-content">
                    {!ops ? (
                        <Loading />
                    ) : (
                        <div className="row op-list">{ops}</div>
                    )}
                    <Link className="biz-add" to="/console/op/add">
                        Add Operator
                    </Link>
                </div>
            </>
        );
    }
}

export default OperatorList;
