// Auth pages logic
document.addEventListener("DOMContentLoaded", () => {
  const mark = document.getElementById("brandMark");
  if (mark) mark.innerHTML = iconHtml("package");

  // eye icon on password toggles
  document.querySelectorAll("#pwToggle").forEach(b => b.innerHTML = iconHtml("eye"));

  const login = document.getElementById("loginForm");
  if (login) login.addEventListener("submit", (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(login));
    LS.set("ll:auth", { email: data.email, loggedInAt: Date.now() });
    toast("Welcome back!");
    setTimeout(() => location.href = "dashboard.html", 800);
  });

  const signup = document.getElementById("signupForm");
  if (signup) signup.addEventListener("submit", (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(signup));
    LS.set("ll:user", { ...getUser(), name: `${data.firstName} ${data.lastName}`, email: data.email });
    LS.set("ll:auth", { email: data.email, loggedInAt: Date.now() });
    toast("Account created!");
    setTimeout(() => location.href = "dashboard.html", 800);
  });
});

function togglePw(id, btn) {
  const input = document.getElementById(id);
  if (input.type === "password") { input.type = "text"; }
  else { input.type = "password"; }
}
