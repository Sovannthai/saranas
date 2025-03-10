<style>
    .chat-upload {
        position: relative;
        padding-top: 34px;
        left: 22px;
        font-size: x-large;
    }

    .customscroll {
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        overflow: scroll;
    }

    .customscroll::-webkit-scrollbar {
       display: none;
    }

    .body-chat {
        height: calc(100vh - 200px);
    }
</style>
<div class="col-lg-9 col-md-8 col-sm-12 body-chat">
    <div class="chat-detail">
        <div class="chat-profile-header clearfix">
            <div class="left">
                <div class="clearfix">
                    <div class="chat-profile-photo">
                        <img src="vendors/images/profile-photo.jpg" alt="" />
                    </div>
                    <div class="chat-profile-name">
                        <h3>Rachel Curtis</h3>
                        <span>New York, USA</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="chat-box">
            <div class="chat-desc customscroll">
                <ul>
                    <li class="clearfix admin_chat">
                        <span class="chat-img ml-2">
                            <img src="vendors/images/chat-img2.jpg" alt="" />
                        </span>
                        <div class="chat-body clearfix">
                            <p id="user_name">Maybe you already have additional info?</p>
                            <div class="chat_time">{{ now()->format('h:i A') }}</div>
                        </div>
                    </li>
                    <li class="clearfix admin_chat">
                        <span class="chat-img">
                            <img src="vendors/images/chat-img2.jpg" alt="" />
                        </span>
                        <div class="chat-body clearfix">
                            <p>
                                It is to early to provide some kind of estimation
                                here. We need user stories.
                            </p>
                            <div class="chat_time">09:40PM</div>
                        </div>
                    </li>
                    <li class="clearfix">
                        <span class="chat-img ml-2">
                            <img src="vendors/images/chat-img1.jpg" alt="" />
                        </span>
                        <div class="chat-body clearfix">
                            <p>
                                We are just writing up the user stories now so
                                will have requirements for you next week. We are
                                just writing up the user stories now so will have
                                requirements for you next week.
                            </p>
                            <div class="chat_time">09:40PM</div>
                        </div>
                    </li>
                    <li class="clearfix upload-file">
                        <span class="chat-img ml-2">
                            <img src="vendors/images/chat-img1.jpg" alt="" />
                        </span>
                        <div class="chat-body clearfix">
                            <div class="upload-file-box clearfix">
                                <div class="left">
                                    <img src="vendors/images/upload-file-img.jpg" alt="" />
                                    <div class="overlay">
                                        <a href="#">
                                            <span><i class="fa fa-angle-down"></i></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="right">
                                    <h3>Big room.jpg</h3>
                                    <a href="#">Download</a>
                                </div>
                            </div>
                            <div class="chat_time">09:40PM</div>
                        </div>
                    </li>
                    <li class="clearfix upload-file admin_chat">
                        <span class="chat-img ml-2">
                            <img src="vendors/images/chat-img2.jpg" alt="" />
                        </span>
                        <div class="chat-body clearfix">
                            <div class="upload-file-box clearfix">
                                <div class="left">
                                    <img src="vendors/images/upload-file-img.jpg" alt="" />
                                    <div class="overlay">
                                        <a href="#">
                                            <span><i class="fa fa-angle-down"></i></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="right">
                                    <h3>Big room.jpg</h3>
                                    <a href="#">Download</a>
                                </div>
                            </div>
                            <div class="chat_time">09:40PM</div>
                        </div>
                    </li>
                </ul>
                <div class="chat-footer">
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="file-upload">
                            <label for="file-input">
                                <i class="fa fa-paperclip chat-upload"></i>
                            </label>
                            <input id="file-input" type="file" style="display: none;" />
                        </div>
                        <div class="chat_text_area">
                            <textarea cols="5" placeholder="Type your message…"></textarea>
                        </div>
                        <div class="chat_send">
                            <button class="btn btn-link" type="submit">
                                {{-- <i class="icon-copy ion-paper-airplane"></i> --}}
                                <img src="https://cdn-icons-png.flaticon.com/512/2343/2343641.png" alt="send">
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
