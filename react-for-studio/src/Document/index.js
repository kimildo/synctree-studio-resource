import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import ReactLoading from 'react-loading';

import Wrapper from './layout/Wrapper';
import Manual from './pages/Manual';
import Error from './include/Error';
@inject('docsStore')
@observer
class Document extends Component {
    render() {
        return (
            <>
                {!!this.props.docsStore.getData ? (
                    <Wrapper>
                        <Manual />
                    </Wrapper>
                ) : !!this.props.docsStore.loadError ? (
                    <Error />
                ) : (
                    <div className="loading">
                        <ReactLoading type="bubbles" color="#3498db" />
                    </div>
                )}
            </>
        );
    }
}

export default Document;
