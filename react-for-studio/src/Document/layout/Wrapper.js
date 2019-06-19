import React, { Component } from 'react';
import Header from './Header';
import Left from './Left';

class Wrapper extends Component {
    render() {
        return (
            <>
                <Header />
                <Left />
                {this.props.children}
            </>
        );
    }
}
export default Wrapper;
