import React, { Component } from 'react';
class BtnAsyncInc extends Component {
    handleClick = type => {
        this.props.handleClick(type);
    };
    checkHandle = type => {
        if (type < 2) {
            this.handleClick(type === 0 ? true : false);
        }
    };
    getClass = type => {
        switch (type) {
            case 0:
            default:
                return 'btn-default';
            case 1:
            case 3:
                return 'btn-danger';
            case 2:
                return 'btn-default disabled';
        }
    };
    render() {
        // 0 : 선택안됨, 1 : 선택됨, 2: 선택불가(비활성) 3: 선택불가(활성)
        const { start, end } = this.props;
        const strClsName = this.getClass(start);
        const endClsName = this.getClass(end);

        return (
            <>
                <button
                    className={`btn ${strClsName}`}
                    onClick={this.checkHandle.bind(this, start)}>
                    Start
                </button>
                <button
                    className={`btn ${endClsName}`}
                    onClick={this.checkHandle.bind(this, end)}>
                    End
                </button>
            </>
        );
    }
}
export default BtnAsyncInc;
