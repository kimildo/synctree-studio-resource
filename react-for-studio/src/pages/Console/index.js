import React from 'react';
import WithLeft from '../../layout/WithLeft';
import { Switch, Route, withRouter } from 'react-router-dom';
import {
    B2b,
    Dashboard,
    Management,
    Marketing,
    Apps,
    BizUnit,
    BunitModify,
    Operator,
    Error404,
} from '../../pages';
const Console = ({ match }) => {
    return (
        <WithLeft>
            <Switch>
                <Route path={`${match.path}/b2b`} component={B2b} />
                <Route
                    exact
                    path={`${match.path}/dashboard`}
                    component={Dashboard}
                />
                <Route exact path={`${match.path}/apps`} component={Apps} />
                <Route
                    exact
                    path={`${match.path}/apps/bunit/:app_id`}
                    component={BizUnit}
                />
                <Route
                    path={`${match.path}/apps/bunit/:app_id/modify/:bunit_id`}
                    component={BunitModify}
                />

                <Route
                    exact
                    path={`${match.path}/management`}
                    component={Management}
                />
                <Route
                    exact
                    path={`${match.path}/marketing`}
                    component={Marketing}
                />
                <Route path={`${match.path}/op`} component={Operator} />
                <Route component={Error404} />
            </Switch>
        </WithLeft>
    );
};

export default withRouter(Console);
