import React, { Component } from 'react';

class Header extends Component {
    render() {
        return (
            <header>
                <div className="logo" />
                {/* <div className="headerBtn">
                    <div className="publicBtn">
                        <p>Public</p>
                    </div>
                    <div className="psotBtn">
                        <button>Run in postman</button>
                    </div>
                    <div className="developeBtn">
                        <button>developement</button>
                        <ul>
                             <!--li에 className="deactive"를 넣을 시 비활성화 버튼 컬러로 바뀝니다.--> 
                            <li>No environment</li>
                            <li className="bottomline" />
                            <li className="deactive">SHARED TEMPLATES</li>
                            <li className="deactive">No shared environments</li>
                            <li className="bottomline" />
                            <li className="deactive">PRIVARE ENVIRONMENTS</li>
                            <li>developement</li>
                        </ul>
                    </div>
                </div> */}
            </header>
        );
    }
}
export default Header;
