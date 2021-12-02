import repository from '~/api/repository';

export default (ctx, inject) => {
  const repositoryWithAxios = repository(ctx.$axios);

  inject('userRepository', repositoryWithAxios('/users'));
};
