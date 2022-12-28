class parallaxTiltEffect {

  constructor({element, tiltEffect}) {

    this.element = element;
    this.container = this.element.querySelector(".container");
    this.size = [300, 360];
    [this.w, this.h] = this.size;

    this.tiltEffect = tiltEffect;

    this.mouseOnComponent = false;

    this.handleMouseMove = this.handleMouseMove.bind(this);
    this.handleMouseEnter = this.handleMouseEnter.bind(this);
    this.handleMouseLeave = this.handleMouseLeave.bind(this);
    this.defaultStates = this.defaultStates.bind(this);
    this.setProperty = this.setProperty.bind(this);
    this.init = this.init.bind(this);

    this.init();
  }

  handleMouseMove(event) {
    const {offsetX, offsetY} = event;

    let X;
    let Y;

    if (this.tiltEffect === "reverse") {
      X = ((offsetX - (this.w/2)) / 3) / 6;
      Y = (-(offsetY - (this.h/2)) / 3) / 6;
    }

    else if (this.tiltEffect === "normal") {
      X = (-(offsetX - (this.w/2)) / 3) / 6;
      Y = ((offsetY - (this.h/2)) / 3) / 6;
    }

    this.setProperty('--rY', X.toFixed(2));
    this.setProperty('--rX', Y.toFixed(2));

    this.setProperty('--bY', (80 - (X/4).toFixed(2)) + '%');
    this.setProperty('--bX', (50 - (Y/4).toFixed(2)) + '%');
  }

  handleMouseEnter() {
    this.mouseOnComponent = true;
    this.container.classList.add("container--active");
  }

  handleMouseLeave() {
    this.mouseOnComponent = false;
    this.defaultStates();
  }

  defaultStates() {
    this.container.classList.remove("container--active");
    this.setProperty('--rY', 0);
    this.setProperty('--rX', 0);
    this.setProperty('--bY', '80%');
    this.setProperty('--bX', '50%');
  }

  setProperty(p, v) {
    return this.container.style.setProperty(p, v);
  }

  init() {
    this.element.addEventListener('mousemove', this.handleMouseMove);
    this.element.addEventListener('mouseenter', this.handleMouseEnter);
    this.element.addEventListener('mouseleave', this.handleMouseLeave);
  }

}
$(document).ready(function() {
  var wrap = document.querySelector('#loader-wrapper');
  setTimeout(() => {
    wrap.classList.add("fade");
    
  }, 1000);
  setTimeout(() => {
    wrap.remove();
  }, 3500);
});

window.onload = function () {
  const $ = e => document.querySelector(e);
  document.querySelector('.wrap--1') ? document.querySelectorAll('.wrap--1').forEach(elem => {
      var wrap = new parallaxTiltEffect({
          element: elem,
          tiltEffect: 'reverse'
      });

      elem.addEventListener("click", function (elem) {
        init_shuffle(this.classList.contains('meta') ? 'meta' : 'astro');
      })
  }) : null;
  var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
  if (document.querySelector('.card-wrapper') && !isSafari) {
    var c_wrap = new parallaxTiltEffect({
      element: document.querySelector('.card-wrapper'),
      tiltEffect: 'reverse'
    })
  }
  document.querySelector("nav a:nth-child(2)") ? document.querySelector("nav a:nth-child(2)").addEventListener("click", function () {
    document.querySelector(".pre_play") ? document.querySelector(".pre_play").classList.remove("pre_play") : null;
      roll_dice();
    }) : null;
  window.p_actions ? setInterval(() => {
      check_actions();
  }, 1000) : null;
  document.querySelector("#start_abort") ? document.querySelector("#start_abort").addEventListener("click", function () {
    session_toggle();
  }) : null;

  !document.querySelector(".dice") && !document.querySelector("#l_form") ? nav_actions() : null;
}

function session_toggle() {
  $.ajax({
    url:        "/helpers.php",
    method:     "POST",
    data:       {'session_toggle' : 1},
    success:    function() {
      setTimeout(() => {
        window.location.href = '/';        
      }, 100);
    }
  });
}

function init_shuffle(s_type) {
  var current_card = document.querySelector('.current_card'),
      card_wrapper = document.querySelector('.card-wrapper'),
      current_desc = document.querySelector('.current_desc');

  $.ajax({
    url:        "/helpers.php",
    method:     "POST",
    data:       {s_type},
    success:    function(result) {
      current_card.src = '/assets/img/' + s_type + '/' + JSON.parse(result)['card'] + '.jpg';
      current_desc.innerHTML = JSON.parse(result)['desc'];
      card_wrapper.classList.add('active');
      setTimeout(() => {
        current_card.classList.add('unfold');
        current_desc.scroll(0, 0);
        current_desc.classList.add('unfold');
      }, 1000);
    },
    error:      function () {
      window.location.href = "/";
    }
  });
}
function alt_deck(c_show) {
  var current_card = document.querySelector('.current_card'),
      card_wrapper = document.querySelector('.card-wrapper'),
      current_desc = document.querySelector('.current_desc'),
      c_hide       = c_show == '.meta' ? '.astro' : '.meta';

    document.querySelectorAll(c_show + ".c_hidden").forEach(elem => {
        elem.classList.remove("c_hidden");
    });
    document.querySelectorAll(c_hide).forEach(elem => {
        elem.classList.add("c_hidden");
    });
    
    card_wrapper.classList.remove('active');
    current_card.classList.remove("unfold");
    current_desc.classList.remove('unfold');
}
function roll_dice() {
  if (document.querySelector(".dice")) {
    const dice = document.querySelector(".die-list");
    toggle_class(dice);

    $.ajax({
      url:        "/helpers.php",
      method:     "POST",
      data:       'rolled',
      success:    function(result) {
        dice.dataset.roll = parseInt(result);
        setTimeout(() => {
          if (parseInt(result) == 5) {
            document.querySelector("main").classList.add("cards");
            document.querySelectorAll(".c_hidden.astro").forEach(elem => {
              elem.classList.remove("c_hidden");
              document.querySelector(".dice").classList.add("fade");
              
              nav_actions();

              setTimeout(() => {
                document.querySelector(".dice") ? document.querySelector(".dice").remove() : null;
              }, 3500);
            });
          }
        }, 1000);
      },
      error:      function () {
        window.location.href = "/";
      }
    });
  }
}
function toggle_class(die) {
  if (!document.querySelector(".dice").classList.contains("show")) {
    document.querySelector(".i_text").classList.remove("show");
    document.querySelector(".dice").classList.add("show");
  }
  die.classList.toggle("odd-roll");
  die.classList.toggle("even-roll");
}
function check_actions() {
  var p_body = document.querySelector(".p_body"),
      p_desc = document.querySelector(".p_img div");

  $.ajax({
    url:        "/helpers.php",
    method:     "POST",
    data:       'check_actions',
    success:    function(result) {
      if (result) {
        var actions_new = JSON.parse(result)['actn'].split(", ");

        if (actions_new.length > p_actions.length) {
  
          while (actions_new.length > p_actions.length) {
  
            let elem    = actions_new[p_actions.length - 1],
                pre_img = elem.substring(elem.indexOf("–") + 2, elem.length);
            window.p_actions.push(elem);
            p_body.innerHTML += '<p>' + elem + '</p>';
            document.querySelector("img.p_test").src = "/assets/img/"     + (pre_img.includes("Дом: ")
                          ? "astro/"  + pre_img.replace("Дом: ", "")      : pre_img.includes("Метазнак: ")
                          ? "meta/"   + pre_img.replace("Метазнак: ", "") : 'card_black') + ".jpg";
                          
            elem == 'Игральный кубик: 5' ? p_body.innerHTML += '<p><span>Выпало 5!</span> Игра началась.</p>'
                                         : p_desc.innerHTML  = JSON.parse(result)['desc'];
          }
        }
      }
    }
  });
}

function is_over() {
  setTimeout(() => {
    $.ajax({
      url:        "/helpers.php",
      method:     "POST",
      data:       'isover',
      success: function(result) {
        result.data == 'End' ? window.location.href = '/' : null;
      },
      error:      function () {
        window.location.href = "/";
      }
    });
  }, 1000);
}

function nav_actions() {
    document.querySelector("nav a:nth-child(1)").addEventListener("click", function () {
        alt_deck(".meta");
    });
    document.querySelector("nav a:nth-child(3)").addEventListener("click", function () {
        alt_deck(".astro");
    });
}