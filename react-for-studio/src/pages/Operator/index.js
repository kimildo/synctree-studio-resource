import React from 'react';
import { Switch, Route, withRouter } from 'react-router-dom';

import MyLoadable from '../../MyLoadable';
import { Error404 } from '../../pages';

const Wrapper = MyLoadable({
    loader: () => import(/* webpackChunkName: "w-o-i" */ './include/Wrapper'),
});

const OperatorList = MyLoadable({
    loader: () => import(/* webpackChunkName: "ol-o-i" */ './OperatorList'),
});

const OperatorAdd = MyLoadable({
    loader: () => import(/* webpackChunkName: "oa-o-i" */ './OperatorAdd'),
});

const OperatorModify = MyLoadable({
    loader: () => import(/* webpackChunkName: "om-o-i" */ './OperatorModify'),
});

const Operator = ({ match }) => {
    return (
        <Wrapper>
            <Switch>
                <Route
                    exact
                    path={`${match.path}/list`}
                    component={OperatorList}
                />
                <Route
                    exact
                    path={`${match.path}/add`}
                    component={OperatorAdd}
                />
                <Route
                    exact
                    path={`${match.path}/modify/:op_id`}
                    component={OperatorModify}
                />
                <Route component={Error404} />
            </Switch>
        </Wrapper>
    );
};

export default withRouter(Operator);
