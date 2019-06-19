import React, { Component } from 'react';
import StepBottom from './StepBottom';
class Step2 extends Component {
    render() {
        return (
            <>
                <h5>
                    해당 url은 파트너사들이 직접 정보를 입력하고 셋팅할 수 있는
                    접속 url과 아이디 암호가 생성됩니다.
                </h5>
                <StepBottom />
            </>
        );
    }
}

export default Step2;
