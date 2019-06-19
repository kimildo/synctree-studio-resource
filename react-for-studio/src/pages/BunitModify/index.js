import React, { Component } from 'react';
import { Switch, Route, withRouter } from 'react-router-dom';
import { inject, observer } from 'mobx-react';

import MyLoadable from '../../MyLoadable';
import { Error404 } from '../../pages';

const Wrapper = MyLoadable({
    loader: () => import(/* webpackChunkName: "w-b-i" */ './Wrapper'),
});
const UnitProperty = MyLoadable({
    loader: () => import(/* webpackChunkName: "u-b-i" */ './UnitProperty'),
});
const Timeline = MyLoadable({
    loader: () => import(/* webpackChunkName: "t-b-i" */ './Timeline'),
});

@withRouter
@inject('bizStore')
@observer
class BunitModify extends Component {
    // componentDidMount() {
    //     this.props.opsStore.getOperators();
    // }
    componentWillUnmount() {
        this.props.bizStore.unsetBizUnit();
    }
    render() {
        const { match } = this.props;
        return (
            <Wrapper>
                <Switch>
                    <Route
                        exact
                        path={`${match.path}/unitprop`}
                        component={UnitProperty}
                    />
                    <Route
                        exact
                        path={`${match.path}/unitflow`}
                        component={Timeline}
                    />
                    <Route component={Error404} />
                </Switch>
            </Wrapper>
        );
    }
}

export default BunitModify;
