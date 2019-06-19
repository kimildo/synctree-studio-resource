import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';

@inject('UserInfoStore')
@observer
class Intro extends Component {
    render() {
        return <div />;
    }
}

export default Intro;
